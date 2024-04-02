<?php

declare(strict_types=1);

namespace App\Modules\Post\Query\Feed\Get;

use App\Components\AllCount;
use App\Modules\Contact\Query\GetUserContactIds\ContactGetUserContactIdsFetcher;
use App\Modules\Contact\Query\GetUserContactIds\ContactGetUserContactIdsQuery;
use App\Modules\Contact\Query\GetUserSubscribeIds\ContactGetUserSubscribeIdsFetcher;
use App\Modules\Contact\Query\GetUserSubscribeIds\ContactGetUserSubscribeIdsQuery;
use App\Modules\Post\Query\Post\GetUserHideIds\PostGetUserHideIdsFetcher;
use App\Modules\Post\Query\Post\GetUserHideIds\PostGetUserHideIdsQuery;
use App\Modules\ResultCountItems;
use App\Modules\Union\Query\Union\GetUserCommunityIds\UnionGetUserCommunityFetcher;
use App\Modules\Union\Query\Union\GetUserCommunityIds\UnionGetUserCommunityQuery;
use App\Modules\Union\Query\Union\GetUserEventIds\UnionGetUserEventFetcher;
use App\Modules\Union\Query\Union\GetUserEventIds\UnionGetUserEventQuery;
use App\Modules\Union\Query\Union\GetUserPlaceIds\UnionGetUserPlaceFetcher;
use App\Modules\Union\Query\Union\GetUserPlaceIds\UnionGetUserPlaceQuery;
use App\Modules\Union\Query\Union\GetUserUnionIds\UnionGetUserUnionFetcher;
use App\Modules\Union\Query\Union\GetUserUnionIds\UnionGetUserUnionQuery;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;

final readonly class FeedGetFetcher
{
    public function __construct(
        private Connection $connection,
        private ContactGetUserContactIdsFetcher $contactGetUserContactIdsFetcher,
        private ContactGetUserSubscribeIdsFetcher $contactGetUserSubscribeIdsFetcher,
        private UnionGetUserCommunityFetcher $unionGetUserCommunityFetcher,
        private UnionGetUserEventFetcher $unionGetUserEventFetcher,
        private UnionGetUserPlaceFetcher $unionGetUserPlaceFetcher,
        private UnionGetUserUnionFetcher $unionGetUserUnionFetcher,
        private PostGetUserHideIdsFetcher $postGetUserHideIdsFetcher,
    ) {}

    /**
     * @throws Exception
     */
    public function fetch(FeedGetQuery $query): ResultCountItems
    {
        $queryBuilder = $this->connection->createQueryBuilder();

        $sqlQuery = $queryBuilder
            ->select('p.*')
            ->from('wall', 'p');

        if ($query->space === 'contacts') {
            if ($ids = $this->getContactIds($query->userId)) {
                $sqlQuery->andWhere($queryBuilder->expr()->in('owner_id', $ids));
            }
        } elseif ($query->space === 'communities') {
            if ($ids = $this->getCommunityIds($query->userId)) {
                $sqlQuery->andWhere($queryBuilder->expr()->in('owner_id', $ids));
            }
        } elseif ($query->space === 'places') {
            if ($ids = $this->getPlaceIds($query->userId)) {
                $sqlQuery->andWhere($queryBuilder->expr()->in('owner_id', $ids));
            }
        } elseif ($query->space === 'events') {
            if ($ids = $this->getEventIds($query->userId)) {
                $sqlQuery->andWhere($queryBuilder->expr()->in('owner_id', $ids));
            }
        } else {
            if ($ids = $this->getAllIds($query->userId)) {
                if (\count($ids) > 25) {
                    $sqlQuery->andWhere($queryBuilder->expr()->in('owner_id', $ids));
                }
            }
        }

        $date = time() - 30 * 24 * 60 * 60;
        $sqlQuery->andWhere('p.date < ' . time() . ' && p.date > ' . $date);

        if ($hidePostIds = $this->getHidePostIds($query->userId)) {
            $sqlQuery->andWhere($queryBuilder->expr()->notIn('id', $hidePostIds));
        }

        $sqlQuery->andWhere('p.hide = 0 && p.deleted_at IS NULL');

        if ($query->content === 'photos') {
            $sqlQuery->andWhere('p.photo_ids IS NOT NULL');
        } elseif ($query->content === 'videos') {
            $sqlQuery->andWhere('p.video_ids IS NOT NULL');
        }

        $order = ($query->sort === 0) ? 'DESC' : 'ASC';

        $result = $sqlQuery
            ->orderBy('p.date', $order)
            ->addOrderBy('p.id', $order)
            ->setMaxResults($query->count)
            ->setFirstResult((int)$query->offset)
            ->executeQuery();
        // $date = time() - 6 * 30 * 24 * 60 * 60;
        // $sqlQuery->andWhere('p.date < ' . time() . ' && p.date > ' . $date);

        $rows = $result->fetchAllAssociative();

        return new ResultCountItems(
            AllCount::get($sqlQuery),
            $rows
        );
    }

    /** @return string[] */
    private function getHidePostIds(int $userId): array
    {
        $ids = $this->postGetUserHideIdsFetcher->fetch(
            new PostGetUserHideIdsQuery($userId)
        );

        return array_map(static fn (int $v) => (string)$v, $ids);
    }

    /** @return string[] */
    private function getContactIds(int $userId): array
    {
        $contactIds = $this->contactGetUserContactIdsFetcher->fetch(
            new ContactGetUserContactIdsQuery($userId)
        );

        $contactIds = array_map(static fn (int $v) => (string)$v, $contactIds);

        $contactSubscribeIds = $this->contactGetUserSubscribeIdsFetcher->fetch(
            new ContactGetUserSubscribeIdsQuery($userId)
        );

        $contactSubscribeIds = array_map(static fn (int $v) => (string)$v, $contactSubscribeIds);

        return array_merge(
            $contactIds,
            $contactSubscribeIds
        );
    }

    /** @return string[] */
    private function getCommunityIds(int $userId): array
    {
        $ids = $this->unionGetUserCommunityFetcher->fetch(
            new UnionGetUserCommunityQuery($userId)
        );

        return array_map(static fn (int $v) => (string)(-1 * $v), $ids);
    }

    /** @return string[] */
    private function getPlaceIds(int $userId): array
    {
        $ids = $this->unionGetUserPlaceFetcher->fetch(
            new UnionGetUserPlaceQuery($userId)
        );

        return array_map(static fn (int $v) => (string)(-1 * $v), $ids);
    }

    /** @return string[] */
    private function getEventIds(int $userId): array
    {
        $ids = $this->unionGetUserEventFetcher->fetch(
            new UnionGetUserEventQuery($userId)
        );

        return array_map(static fn (int $v) => (string)(-1 * $v), $ids);
    }

    /** @return string[] */
    private function getAllIds(int $userId): array
    {
        $ids = $this->unionGetUserUnionFetcher->fetch(
            new UnionGetUserUnionQuery($userId)
        );

        $ids = array_map(static fn (int $v) => (string)(-1 * $v), $ids);

        /** @var string[] $contactIds */
        $contactIds = $this->contactGetUserContactIdsFetcher->fetch(
            new ContactGetUserContactIdsQuery($userId)
        );

        return array_values(
            array_merge(
                $ids,
                $contactIds
            )
        );
    }
}
