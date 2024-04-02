<?php

declare(strict_types=1);

namespace App\Modules\Union\Entity\UnionCategory;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

class UnionCategoryTranslateRepository
{
    /** @var EntityRepository<UnionCategoryTranslate> */
    private EntityRepository $repo;
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->repo = $em->getRepository(UnionCategoryTranslate::class);
        $this->em = $em;
    }

    public function findById(int $id): ?UnionCategoryTranslate
    {
        return $this->repo->findOneBy(['id' => $id]);
    }

    public function add(UnionCategoryTranslate $unionCategoryTranslate): void
    {
        $this->em->persist($unionCategoryTranslate);
    }

    public function remove(UnionCategoryTranslate $unionCategoryTranslate): void
    {
        $this->em->remove($unionCategoryTranslate);
    }
}
