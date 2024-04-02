<?php

declare(strict_types=1);

namespace App\Modules\Audio\Console\Typesense;

use App\Modules\Audio\Entity\AudioAlbum\AudioAlbum;
use App\Modules\Audio\Query\AudioAlbumUnion\GetByAlbumIds\AudioAlbumUnionGetByAlbumIdsFetcher;
use App\Modules\Audio\Query\AudioAlbumUnion\GetByAlbumIds\AudioAlbumUnionGetByAlbumIdsQuery;
use App\Modules\Audio\Service\Typesense\AudioAlbum\AudioAlbumCollection;
use App\Modules\Audio\Service\Typesense\AudioAlbum\AudioAlbumDocument;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

final class AudioAlbumRefreshCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly AudioAlbumCollection $audioAlbumCollection,
        private readonly AudioAlbumUnionGetByAlbumIdsFetcher $audioAlbumUnionGetByAlbumIdsFetcher
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('typesense:audio-album:refresh')
            ->setDescription('Create audio-album collection in Typesense');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->recreateSchema();

        $maxId = null;
        $count = 0;

        try {
            while (true) {
                ++$count;

                $audioAlbums = $this->getAudioAlbums($maxId);

                if (\count($audioAlbums) === 0) {
                    return 0;
                }

                $documents = [];

                foreach ($audioAlbums as $audioAlbum) {
                    $maxId = $audioAlbum->getId();

                    $title = trim($audioAlbum->getName() . ' ' . ($audioAlbum->getVersion() ?? ''));

                    $documents[] = new AudioAlbumDocument(
                        identifier: $audioAlbum->getId(),
                        unionIds: $this->getUnionIds($audioAlbum->getId()),
                        title: $title,
                        artists: $audioAlbum->getArtists(),
                        year: $audioAlbum->getYear(),
                        isAlbum: $audioAlbum->getAudioCount() > 1, // todo
                    );
                }

                $this->audioAlbumCollection->upsertDocuments($documents);

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
            $this->audioAlbumCollection->deleteSchema();
        } catch (Throwable) {
        }

        try {
            $this->audioAlbumCollection->createSchema();
        } catch (Throwable) {
        }
    }

    /** @return AudioAlbum[] */
    private function getAudioAlbums(?int $maxId): array
    {
        $this->em->clear();

        $criteria = Criteria::create();

        if (null !== $maxId) {
            $criteria->andWhere(Criteria::expr()->lt('id', $maxId));
        }

        /** @var AudioAlbum[] $audioAlbums */
        $audioAlbums = $this->em->getRepository(AudioAlbum::class)
            ->matching(
                $criteria
                    ->andWhere(Criteria::expr()->isNull('deletedAt'))
                    ->orderBy(['id' => 'DESC'])
                    ->setMaxResults(5_000)
            );

        /** @var AudioAlbum[] $result */
        $result = [];

        foreach ($audioAlbums as $audioAlbum) {
            $result[] = $audioAlbum;
        }

        return $result;
    }

    private function getUnionIds(int $albumId): array
    {
        $albumUnions = $this->audioAlbumUnionGetByAlbumIdsFetcher->fetch(
            new AudioAlbumUnionGetByAlbumIdsQuery(
                albumIds: [$albumId]
            )
        );

        $result = [];

        foreach ($albumUnions as $albumUnion) {
            $result[] = $albumUnion['union_id'];
        }

        return $result;
    }
}
