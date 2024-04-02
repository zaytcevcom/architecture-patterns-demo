<?php

declare(strict_types=1);

namespace App\Modules\Union\Entity\UnionUser;

use App\Modules\Union\Entity\Union\Union;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use ZayMedia\Shared\Http\Exception\DomainExceptionModule;

class UnionUserRepository
{
    /**
     * @var EntityRepository<UnionUser>
     */
    private EntityRepository $repo;
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->repo = $em->getRepository(UnionUser::class);
        $this->em = $em;
    }

    public function countTodayByUserId(int $userId): int
    {
        $timeFrom = strtotime(date('Y-m-d'));

        return $this->repo
            ->matching(
                Criteria::create()
                    ->where(Criteria::expr()->eq('userId', $userId))
                    ->andWhere(Criteria::expr()->gt('timeJoin', $timeFrom))
            )
            ->count();
    }

    public function countByUserId(int $userId): int
    {
        return $this->repo->count(['userId' => $userId]);
    }

    public function countCommunitiesByUserId(int $userId): int
    {
        return $this->repo->count(['userId' => $userId]);
    }

    public function countEventsByUserId(int $userId): int
    {
        return $this->repo->count(['userId' => $userId]);
    }

    public function countPlacesByUserId(int $userId): int
    {
        return $this->repo->count(['userId' => $userId]);
    }

    public function countInviteTodayByUserId(int $userId): int
    {
        $timeFrom = strtotime(date('Y-m-d'));

        return $this->repo
            ->matching(
                Criteria::create()
                    ->where(Criteria::expr()->eq('userId', $userId))
                    ->andWhere(Criteria::expr()->gt('timeJoin', $timeFrom))
            )
            ->count();
    }

    public function countInviteByUserId(int $userId): int
    {
        return $this->repo
            ->matching(
                Criteria::create()
                    ->where(Criteria::expr()->eq('userId', $userId))
                    ->andWhere(Criteria::expr()->notIn('role', [Union::roleRequest(), Union::roleInvite()]))
            )
            ->count();
    }

    public function countByUnionId(int $unionId): int
    {
        return $this->repo->count(['unionId' => $unionId]);
    }

    public function getById(int $id): UnionUser
    {
        if (!$unionUser = $this->findById($id)) {
            throw new DomainExceptionModule(
                module: 'union',
                message: 'error.union.union_user_not_found',
                code: 1
            );
        }

        return $unionUser;
    }

    public function findById(int $id): ?UnionUser
    {
        return $this->repo->findOneBy(['id' => $id]);
    }

    public function findByUserAndUnionIds(int $userId, int $unionId): ?UnionUser
    {
        return $this->repo->findOneBy(['userId' => $userId, 'unionId' => $unionId]);
    }

    public function isMember(int $userId, int $unionId): bool
    {
        return $this->repo->findOneBy(['userId' => $userId, 'unionId' => $unionId]) !== null;
    }

    public function add(UnionUser $unionUser): void
    {
        $this->em->persist($unionUser);
    }

    public function remove(UnionUser $unionUser): void
    {
        $this->em->remove($unionUser);
    }
}
