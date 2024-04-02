<?php

declare(strict_types=1);

namespace App\Modules\Union\Entity\UnionPlaceInfo;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use ZayMedia\Shared\Http\Exception\DomainExceptionModule;

class UnionPlaceInfoRepository
{
    /**
     * @var EntityRepository<UnionPlaceInfo>
     */
    private EntityRepository $repo;
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->repo = $em->getRepository(UnionPlaceInfo::class);
        $this->em = $em;
    }

    public function getById(int $id): UnionPlaceInfo
    {
        if (!$unionPlaceInfo = $this->findById($id)) {
            throw new DomainExceptionModule(
                module: 'union',
                message: 'error.union.union_place_info_not_found',
                code: 1
            );
        }

        return $unionPlaceInfo;
    }

    public function findById(int $id): ?UnionPlaceInfo
    {
        return $this->repo->findOneBy(['id' => $id]);
    }

    public function getByUnionId(int $unionId): UnionPlaceInfo
    {
        if (!$unionPlaceInfo = $this->findByUnionId($unionId)) {
            throw new DomainExceptionModule(
                module: 'union',
                message: 'error.union.union_place_info_not_found',
                code: 1
            );
        }

        return $unionPlaceInfo;
    }

    public function findByUnionId(int $unionId): ?UnionPlaceInfo
    {
        return $this->repo->findOneBy(['unionId' => $unionId]);
    }

    public function add(UnionPlaceInfo $unionPlaceInfo): void
    {
        $this->em->persist($unionPlaceInfo);
    }

    public function remove(UnionPlaceInfo $unionPlaceInfo): void
    {
        $this->em->remove($unionPlaceInfo);
    }
}
