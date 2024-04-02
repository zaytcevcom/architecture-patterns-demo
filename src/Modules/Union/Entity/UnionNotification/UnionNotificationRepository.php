<?php

declare(strict_types=1);

namespace App\Modules\Union\Entity\UnionNotification;

use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use ZayMedia\Shared\Http\Exception\DomainExceptionModule;

class UnionNotificationRepository
{
    /**
     * @var EntityRepository<UnionNotification>
     */
    private EntityRepository $repo;
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->repo = $em->getRepository(UnionNotification::class);
        $this->em = $em;
    }

    public function countTodayByUserId(int $userId): int
    {
        $timeFrom = strtotime(date('Y-m-d'));

        return $this->repo
            ->matching(
                Criteria::create()
                    ->where(Criteria::expr()->eq('userId', $userId))
                    ->andWhere(Criteria::expr()->gt('time', $timeFrom))
            )
            ->count();
    }

    public function countByUserId(int $userId): int
    {
        return $this->repo->count(['userId' => $userId]);
    }

    public function getById(int $id): UnionNotification
    {
        if (!$unionNotification = $this->findById($id)) {
            throw new DomainExceptionModule(
                module: 'union',
                message: 'error.union.union_notification_not_found',
                code: 1
            );
        }

        return $unionNotification;
    }

    public function findById(int $id): ?UnionNotification
    {
        return $this->repo->findOneBy([
            'id' => $id,
        ]);
    }

    public function findByUserAndUnionIds(int $userId, int $unionId): ?UnionNotification
    {
        return $this->repo->findOneBy(['userId' => $userId, 'unionId' => $unionId]);
    }

    public function add(UnionNotification $unionNotification): void
    {
        $this->em->persist($unionNotification);
    }

    public function remove(UnionNotification $unionNotification): void
    {
        $this->em->remove($unionNotification);
    }
}
