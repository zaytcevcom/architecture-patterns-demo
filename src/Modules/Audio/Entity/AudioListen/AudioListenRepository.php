<?php

declare(strict_types=1);

namespace App\Modules\Audio\Entity\AudioListen;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

class AudioListenRepository
{
    /** @var EntityRepository<AudioListen> */
    private EntityRepository $repo;
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->repo = $em->getRepository(AudioListen::class);
        $this->em = $em;
    }

    public function findByAudioAndUser(int $audioId, int $userId): ?AudioListen
    {
        return $this->repo->findOneBy([
            'audioId' => $audioId,
            'userId' => $userId,
        ]);
    }

    public function add(AudioListen $audioListen): void
    {
        $this->em->persist($audioListen);
    }

    public function remove(AudioListen $audioListen): void
    {
        $this->em->remove($audioListen);
    }
}
