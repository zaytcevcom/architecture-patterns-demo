<?php

declare(strict_types=1);

namespace App\Modules\Audio\Entity\AudioGenre;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use ZayMedia\Shared\Http\Exception\DomainExceptionModule;

class AudioGenreRepository
{
    /**
     * @var EntityRepository<AudioGenre>
     */
    private EntityRepository $repo;
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->repo = $em->getRepository(AudioGenre::class);
        $this->em = $em;
    }

    public function getById(int $id): AudioGenre
    {
        if (!$audioGenre = $this->findById($id)) {
            throw new DomainExceptionModule(
                module: 'audio',
                message: 'error.audio.audio_genre_not_found',
                code: 1
            );
        }

        return $audioGenre;
    }

    public function findById(int $id): ?AudioGenre
    {
        return $this->repo->findOneBy(['id' => $id]);
    }

    public function add(AudioGenre $audioGenre): void
    {
        $this->em->persist($audioGenre);
    }

    public function remove(AudioGenre $audioGenre): void
    {
        $this->em->remove($audioGenre);
    }
}
