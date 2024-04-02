<?php

declare(strict_types=1);

namespace App\Modules\Union\Entity\UnionSphereCategory;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use ZayMedia\Shared\Http\Exception\DomainExceptionModule;

class UnionSphereCategoryRepository
{
    /**
     * @var EntityRepository<UnionSphereCategory>
     */
    private EntityRepository $repo;
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->repo = $em->getRepository(UnionSphereCategory::class);
        $this->em = $em;
    }

    public function getById(int $id): UnionSphereCategory
    {
        if (!$unionSphereCategory = $this->findById($id)) {
            throw new DomainExceptionModule(
                module: 'union',
                message: 'error.union.union_sphere_category_not_found',
                code: 1
            );
        }

        return $unionSphereCategory;
    }

    public function findById(int $id): ?UnionSphereCategory
    {
        return $this->repo->findOneBy(['id' => $id]);
    }

    public function add(UnionSphereCategory $unionSphereCategory): void
    {
        $this->em->persist($unionSphereCategory);
    }

    public function remove(UnionSphereCategory $unionSphereCategory): void
    {
        $this->em->remove($unionSphereCategory);
    }
}
