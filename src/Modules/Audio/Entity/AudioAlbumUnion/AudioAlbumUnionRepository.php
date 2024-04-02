<?php

declare(strict_types=1);

namespace App\Modules\Audio\Entity\AudioAlbumUnion;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

class AudioAlbumUnionRepository
{
    /** @var EntityRepository<AudioAlbumUnion> */
    private EntityRepository $repo;
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->repo = $em->getRepository(AudioAlbumUnion::class);
        $this->em = $em;
    }

    public function findByAlbumAndUnion(int $albumId, int $unionId): ?AudioAlbumUnion
    {
        return $this->repo->findOneBy([
            'albumId' => $albumId,
            'unionId' => $unionId,
        ]);
    }

    public function add(AudioAlbumUnion $audioAlbumUnion): void
    {
        $this->em->persist($audioAlbumUnion);
    }

    public function remove(AudioAlbumUnion $audioAlbumUnion): void
    {
        $this->em->remove($audioAlbumUnion);
    }
}
