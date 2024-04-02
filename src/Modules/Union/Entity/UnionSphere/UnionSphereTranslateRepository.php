<?php

declare(strict_types=1);

namespace App\Modules\Union\Entity\UnionSphere;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

class UnionSphereTranslateRepository
{
    /**
     * @var EntityRepository<UnionSphereTranslate>
     */
    private EntityRepository $repo;
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->repo = $em->getRepository(UnionSphereTranslate::class);
        $this->em = $em;
    }

    public function findById(int $id): ?UnionSphereTranslate
    {
        return $this->repo->findOneBy(['id' => $id]);
    }

    public function add(UnionSphereTranslate $unionSphereTranslate): void
    {
        $this->em->persist($unionSphereTranslate);
    }

    public function remove(UnionSphereTranslate $unionSphereTranslate): void
    {
        $this->em->remove($unionSphereTranslate);
    }
}
