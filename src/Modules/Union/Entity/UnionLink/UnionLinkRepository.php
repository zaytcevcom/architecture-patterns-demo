<?php

declare(strict_types=1);

namespace App\Modules\Union\Entity\UnionLink;

use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use ZayMedia\Shared\Http\Exception\DomainExceptionModule;

class UnionLinkRepository
{
    /**
     * @var EntityRepository<UnionLink>
     */
    private EntityRepository $repo;
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->repo = $em->getRepository(UnionLink::class);
        $this->em = $em;
    }

    public function countByUnionId(int $unionId): int
    {
        return $this->repo
            ->matching(
                Criteria::create()
                    ->andWhere(Criteria::expr()->eq('unionId', $unionId))
                    ->andWhere(Criteria::expr()->isNull('deletedAt'))
            )
            ->count();
    }

    public function getById(int $id): UnionLink
    {
        if (!$unionLink = $this->findById($id)) {
            throw new DomainExceptionModule(
                module: 'union',
                message: 'error.union.union_link_not_found',
                code: 1
            );
        }

        return $unionLink;
    }

    public function findById(int $id): ?UnionLink
    {
        return $this->repo->findOneBy(['id' => $id]);
    }

    public function add(UnionLink $unionLink): void
    {
        $this->em->persist($unionLink);
    }

    public function remove(UnionLink $unionLink): void
    {
        $this->em->remove($unionLink);
    }
}
