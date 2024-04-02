<?php

declare(strict_types=1);

namespace App\Modules\Audio\Console\Typesense;

use App\Modules\Audio\Entity\AudioPlaylist\AudioPlaylist;
use App\Modules\Audio\Service\Typesense\AudioPlaylist\AudioPlaylistCollection;
use App\Modules\Audio\Service\Typesense\AudioPlaylist\AudioPlaylistDocument;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

final class AudioPlaylistRefreshCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly AudioPlaylistCollection $audioPlaylistCollection
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('typesense:audio-playlist:refresh')
            ->setDescription('Create audio-playlist collection in Typesense');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->recreateSchema();

        $maxId = null;
        $count = 0;

        try {
            while (true) {
                ++$count;

                $audioPlaylists = $this->getAudioPlaylists($maxId);

                if (\count($audioPlaylists) === 0) {
                    return 0;
                }

                $documents = [];

                foreach ($audioPlaylists as $audioPlaylist) {
                    $maxId = $audioPlaylist->getId();

                    $title = trim($audioPlaylist->getName());

                    $documents[] = new AudioPlaylistDocument(
                        identifier: $audioPlaylist->getId(),
                        unionId: $audioPlaylist->getUnionId(),
                        title: $title,
                        artists: $audioPlaylist->getArtists(),
                    );
                }

                $this->audioPlaylistCollection->upsertDocuments($documents);

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
            $this->audioPlaylistCollection->deleteSchema();
        } catch (Throwable) {
        }

        try {
            $this->audioPlaylistCollection->createSchema();
        } catch (Throwable) {
        }
    }

    /** @return AudioPlaylist[] */
    private function getAudioPlaylists(?int $maxId): array
    {
        $this->em->clear();

        $criteria = Criteria::create();

        if (null !== $maxId) {
            $criteria->andWhere(Criteria::expr()->lt('id', $maxId));
        }

        /** @var AudioPlaylist[] $audioPlaylists */
        $audioPlaylists = $this->em->getRepository(AudioPlaylist::class)
            ->matching(
                $criteria
                    ->andWhere(Criteria::expr()->isNull('deletedAt'))
                    ->orderBy(['id' => 'DESC'])
                    ->setMaxResults(5_000)
            );

        /** @var AudioPlaylist[] $result */
        $result = [];

        foreach ($audioPlaylists as $audioPlaylist) {
            $result[] = $audioPlaylist;
        }

        return $result;
    }
}
