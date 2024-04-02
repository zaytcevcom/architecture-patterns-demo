<?php

declare(strict_types=1);

namespace App\Modules\Audio\Entity\AudioPlaylist;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use ZayMedia\Shared\Http\Exception\DomainExceptionModule;

class AudioPlaylistRepository
{
    /**
     * @var EntityRepository<AudioPlaylist>
     */
    private EntityRepository $repo;
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->repo = $em->getRepository(AudioPlaylist::class);
        $this->em = $em;
    }

    public function getById(int $id): AudioPlaylist
    {
        if (!$audioPlaylist = $this->findById($id)) {
            throw new DomainExceptionModule(
                module: 'audio',
                message: 'error.audio.audio_playlist_not_found',
                code: 1
            );
        }

        return $audioPlaylist;
    }

    public function findById(int $id): ?AudioPlaylist
    {
        return $this->repo->findOneBy(['id' => $id]);
    }

    public function add(AudioPlaylist $audioPlaylist): void
    {
        $this->em->persist($audioPlaylist);
    }

    public function remove(AudioPlaylist $audioPlaylist): void
    {
        $this->em->remove($audioPlaylist);
    }
}
