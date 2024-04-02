<?php

declare(strict_types=1);

namespace App\Modules\Audio\Console\Typesense;

use App\Modules\Audio\Entity\Audio\Audio;
use App\Modules\Audio\Query\AudioAlbum\GetByIds\AudioAlbumGetByIdsFetcher;
use App\Modules\Audio\Query\AudioAlbum\GetByIds\AudioAlbumGetByIdsQuery;
use App\Modules\Audio\Query\AudioUnion\GetByAudioIds\AudioUnionGetByAudioIdsFetcher;
use App\Modules\Audio\Query\AudioUnion\GetByAudioIds\AudioUnionGetByAudioIdsQuery;
use App\Modules\Audio\Service\Typesense\Audio\AudioCollection;
use App\Modules\Audio\Service\Typesense\Audio\AudioDocument;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

final class AudioRefreshCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly AudioCollection $audioCollection,
        private readonly AudioAlbumGetByIdsFetcher $audioAlbumGetByIdsFetcher,
        private readonly AudioUnionGetByAudioIdsFetcher $audioUnionGetByAudioIdsFetcher
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('typesense:audio:refresh')
            ->setDescription('Create audio collection in Typesense');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->recreateSchema();

        $maxId = null;
        $count = 0;

        try {
            while (true) {
                ++$count;

                $audios = $this->getAudios($maxId);

                if (\count($audios) === 0) {
                    return 0;
                }

                $documents = [];

                $albumIds = [];

                foreach ($audios as $audio) {
                    if ($albumId = $audio->getAlbum()?->getId()) {
                        $albumIds[] = $albumId;
                    }
                }

                $years = $this->getAlbumYears($albumIds);

                foreach ($audios as $audio) {
                    $maxId = $audio->getId();

                    $title = trim($audio->getName() . ' ' . ($audio->getVersion() ?? ''));
                    $year = ($albumId = $audio->getAlbum()?->getId()) ? $years[$albumId] : 1970;

                    $documents[] = new AudioDocument(
                        identifier: $audio->getId(),
                        unionIds: $this->getUnionIds($audio->getId()),
                        title: $title,
                        artists: $audio->getArtists(),
                        year: $year,
                    );
                }

                $this->audioCollection->upsertDocuments($documents);

                $output->writeln('Count: ' . $count);
            }
        } catch (Throwable $throwable) {
            $output->writeln($throwable->getMessage());
        }

        return 0;
    }

    private function recreateSchema(): void
    {
        try {
            $this->audioCollection->deleteSchema();
        } catch (Throwable) {
        }

        try {
            $this->audioCollection->createSchema();
        } catch (Throwable) {
        }
    }

    /** @return Audio[] */
    private function getAudios(?int $maxId): array
    {
        $this->em->clear();

        $criteria = Criteria::create();

        if (null !== $maxId) {
            $criteria->andWhere(Criteria::expr()->lt('id', $maxId));
        }

        /** @var Audio[] $audios */
        $audios = $this->em->getRepository(Audio::class)
            ->matching(
                $criteria
                    ->andWhere(Criteria::expr()->isNull('deletedAt'))
                    ->orderBy(['id' => 'DESC'])
                    ->setMaxResults(5_000)
            );

        /** @var Audio[] $result */
        $result = [];

        foreach ($audios as $audio) {
            $result[] = $audio;
        }

        return $result;
    }

    /** @return array<int, int> */
    private function getAlbumYears(array $ids): array
    {
        $ids = array_unique($ids);

        $result = [];
        $chunks = array_chunk($ids, 1000);

        foreach ($chunks as $chunk) {
            /** @var array{id: int, year: int}[] $albums */
            $albums = $this->audioAlbumGetByIdsFetcher->fetch(
                new AudioAlbumGetByIdsQuery($chunk)
            );

            foreach ($albums as $album) {
                if (isset($result[$album['id']])) {
                    continue;
                }

                $result[$album['id']] = $album['year'];
            }
        }

        return $result;
    }

    private function getUnionIds(int $audioId): array
    {
        $audioUnions = $this->audioUnionGetByAudioIdsFetcher->fetch(
            new AudioUnionGetByAudioIdsQuery(
                audioIds: [$audioId]
            )
        );

        $result = [];

        foreach ($audioUnions as $audioUnion) {
            $result[] = $audioUnion['union_id'];
        }

        return $result;
    }
}
