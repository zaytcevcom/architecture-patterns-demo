<?php

declare(strict_types=1);

namespace App\Modules\Audio\Entity\AudioUnion;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

class AudioUnionRepository
{
    /** @var EntityRepository<AudioUnion> */
    private EntityRepository $repo;
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->repo = $em->getRepository(AudioUnion::class);
        $this->em = $em;
    }

    public function findByAudioAndUnion(int $audioId, int $unionId): ?AudioUnion
    {
        return $this->repo->findOneBy([
            'audioId' => $audioId,
            'unionId' => $unionId,
        ]);
    }

    public function add(AudioUnion $audioUnion): void
    {
        $this->em->persist($audioUnion);
    }

    public function remove(AudioUnion $audioUnion): void
    {
        $this->em->remove($audioUnion);
    }
}
