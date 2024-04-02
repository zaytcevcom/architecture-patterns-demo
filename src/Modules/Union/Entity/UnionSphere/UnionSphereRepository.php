<?php

declare(strict_types=1);

namespace App\Modules\Union\Entity\UnionSphere;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use ZayMedia\Shared\Http\Exception\DomainExceptionModule;

class UnionSphereRepository
{
    /**
     * @var EntityRepository<UnionSphere>
     */
    private EntityRepository $repo;
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->repo = $em->getRepository(UnionSphere::class);
        $this->em = $em;
    }

    public function getById(int $id): UnionSphere
    {
        if (!$unionSphere = $this->findById($id)) {
            throw new DomainExceptionModule(
                module: 'union',
                message: 'error.union.union_sphere_not_found',
                code: 1
            );
        }

        return $unionSphere;
    }

    public function findById(int $id): ?UnionSphere
    {
        return $this->repo->findOneBy(['id' => $id]);
    }

    public function add(UnionSphere $unionSphere): void
    {
        $this->em->persist($unionSphere);
    }

    public function remove(UnionSphere $unionSphere): void
    {
        $this->em->remove($unionSphere);
    }
}
