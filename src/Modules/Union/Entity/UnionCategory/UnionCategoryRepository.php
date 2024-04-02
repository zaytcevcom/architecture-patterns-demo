<?php

declare(strict_types=1);

namespace App\Modules\Union\Entity\UnionCategory;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use ZayMedia\Shared\Http\Exception\DomainExceptionModule;

class UnionCategoryRepository
{
    /**
     * @var EntityRepository<UnionCategory>
     */
    private EntityRepository $repo;
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->repo = $em->getRepository(UnionCategory::class);
        $this->em = $em;
    }

    public function getById(int $id): UnionCategory
    {
        if (!$unionCategory = $this->findById($id)) {
            throw new DomainExceptionModule(
                module: 'union',
                message: 'error.union.union_category_not_found',
                code: 1
            );
        }

        return $unionCategory;
    }

    public function findById(int $id): ?UnionCategory
    {
        return $this->repo->findOneBy(['id' => $id]);
    }

    public function add(UnionCategory $unionCategory): void
    {
        $this->em->persist($unionCategory);
    }

    public function remove(UnionCategory $unionCategory): void
    {
        $this->em->remove($unionCategory);
    }
}
