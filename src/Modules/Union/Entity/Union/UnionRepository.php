<?php

declare(strict_types=1);

namespace App\Modules\Union\Entity\Union;

use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use ZayMedia\Shared\Http\Exception\DomainExceptionModule;

class UnionRepository
{
    /**
     * @var EntityRepository<Union>
     */
    private EntityRepository $repo;
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->repo = $em->getRepository(Union::class);
        $this->em = $em;
    }

    public function getById(int $id): Union
    {
        if (!$union = $this->findById($id)) {
            throw new DomainExceptionModule(
                module: 'union',
                message: 'error.union.union_not_found',
                code: 1
            );
        }

        return $union;
    }

    public function findById(int $id): ?Union
    {
        return $this->repo->findOneBy(['id' => $id]);
    }

    public function findByScreenName(string $screenName): ?Union
    {
        return $this->repo->findOneBy(['screenName' => $screenName]);
    }

    public function countCommunityByCategoryId(int $categoryId): int
    {
        return $this->repo
            ->matching(
                Criteria::create()
                    ->andWhere(Criteria::expr()->eq('categoryId', $categoryId))
                    ->andWhere(Criteria::expr()->neq('photo', null))
                    ->andWhere(Criteria::expr()->eq('type', Union::typeCommunity()))
            )
            ->count();
    }

    public function add(Union $union): void
    {
        $this->em->persist($union);
    }

    public function remove(Union $union): void
    {
        $this->em->remove($union);
    }

    public function clear(): void
    {
        $this->em->clear();
    }
}
