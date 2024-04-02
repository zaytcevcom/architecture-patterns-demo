<?php

declare(strict_types=1);

namespace App\Modules\Union\Entity\UnionEventInfo;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use ZayMedia\Shared\Http\Exception\DomainExceptionModule;

class UnionEventInfoRepository
{
    /**
     * @var EntityRepository<UnionEventInfo>
     */
    private EntityRepository $repo;
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->repo = $em->getRepository(UnionEventInfo::class);
        $this->em = $em;
    }

    public function getById(int $id): UnionEventInfo
    {
        if (!$unionEventInfo = $this->findById($id)) {
            throw new DomainExceptionModule(
                module: 'union',
                message: 'error.union.union_event_info_not_found',
                code: 1
            );
        }

        return $unionEventInfo;
    }

    public function findById(int $id): ?UnionEventInfo
    {
        return $this->repo->findOneBy(['id' => $id]);
    }

    public function getByUnionId(int $unionId): UnionEventInfo
    {
        if (!$unionEventInfo = $this->findByUnionId($unionId)) {
            throw new DomainExceptionModule(
                module: 'union',
                message: 'error.union.union_event_info_not_found',
                code: 1
            );
        }

        return $unionEventInfo;
    }

    public function findByUnionId(int $unionId): ?UnionEventInfo
    {
        return $this->repo->findOneBy(['unionId' => $unionId]);
    }

    public function getPlaceIdByEventId(int $eventId): int
    {
        if (!$id = $this->findPlaceIdByEventId($eventId)) {
            throw new DomainExceptionModule(
                module: 'union',
                message: 'error.union.union_event_info_not_found',
                code: 1
            );
        }

        return $id;
    }

    public function findPlaceIdByEventId(int $eventId): ?int
    {
        $info = $this->repo->findOneBy(['unionId' => $eventId]);

        if (null !== $info) {
            return -1 * $info->getOwnerId();
        }

        return null;
    }

    public function updateAllPlaceByEventId(int $unionId, int $placeId): void
    {
        $this->em->createQueryBuilder()
            ->update(UnionEventInfo::class, 'ue')
            ->set('ue.ownerId', ':placeId')
            ->andWhere('ue.unionId = :unionId')
            ->setParameter(':unionId', $unionId)
            ->setParameter(':placeId', -1 * $placeId)
            ->getQuery()->execute();
    }

    public function removeAllEventsByPlaceId(int $unionId, int $placeId): void
    {
        $this->em->createQueryBuilder()
            ->delete(UnionEventInfo::class, 'ue')
            ->andWhere('ue.ownerId = :placeId')
            ->andWhere('ue.unionId = :unionId')
            ->setParameter(':unionId', $unionId)
            ->setParameter(':placeId', -1 * $placeId)
            ->getQuery()->execute();
    }

    public function add(UnionEventInfo $unionEventInfo): void
    {
        $this->em->persist($unionEventInfo);
    }

    public function remove(UnionEventInfo $unionEventInfo): void
    {
        $this->em->remove($unionEventInfo);
    }
}
