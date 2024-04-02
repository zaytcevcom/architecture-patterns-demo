<?php

declare(strict_types=1);

namespace App\Modules\Union\Entity\UnionContact;

use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use ZayMedia\Shared\Http\Exception\DomainExceptionModule;

class UnionContactRepository
{
    /**
     * @var EntityRepository<UnionContact>
     */
    private EntityRepository $repo;
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->repo = $em->getRepository(UnionContact::class);
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

    public function getById(int $id): UnionContact
    {
        if (!$unionContact = $this->findById($id)) {
            throw new DomainExceptionModule(
                module: 'union',
                message: 'error.union.union_contact_not_found',
                code: 1
            );
        }

        return $unionContact;
    }

    public function findById(int $id): ?UnionContact
    {
        return $this->repo->findOneBy(['id' => $id]);
    }

    public function add(UnionContact $unionContact): void
    {
        $this->em->persist($unionContact);
    }

    public function remove(UnionContact $unionContact): void
    {
        $this->em->remove($unionContact);
    }
}
