<?php

declare(strict_types=1);

namespace App\Http\Action\Unifier\Union;

use App\Modules\Identity\Query\GetByIds\IdentityGetByIdsFetcherCached;
use App\Modules\Identity\Query\GetByIds\IdentityGetByIdsQuery;
use App\Modules\Identity\Service\UserSerializer;
use App\Modules\Union\Service\UnionContactSerializer;
use ZayMedia\Shared\Http\Unifier\UnifierInterface;

final readonly class UnionContactUnifier implements UnifierInterface
{
    public function __construct(
        private IdentityGetByIdsFetcherCached $userFetcher,
        private UserSerializer $userSerializer,
        private UnionContactSerializer $unionContactSerializer,
    ) {}

    public function unifyOne(?int $userId, ?array $item): array
    {
        /** @var array{array} $result */
        $result = $this->unify($userId, (null !== $item) ? [$item] : []);
        return (isset($result[0])) ? $result[0] : [];
    }

    public function unify(?int $userId, array $items): array
    {
        $items = $this->unionContactSerializer->serializeItems($items);

        return $this->mapUsers(
            items: $items,
            users: $this->getUsers($this->getUserIds($items))
        );
    }

    private function getUsers(array $ids): array
    {
        return $this->userSerializer->serializeItems(
            $this->userFetcher->fetch(
                new IdentityGetByIdsQuery($ids)
            )
        );
    }

    private function mapUsers(array $items, array $users): array
    {
        /** @var array{userId:int}[] $items */
        foreach ($items as $key => $item) {
            $items[$key]['user'] = null;

            /** @var array{id:int} $user */
            foreach ($users as $user) {
                if ($item['userId'] === $user['id']) {
                    $items[$key]['user'] = $user;
                    break;
                }
            }

            if (isset($items[$key]['userId'])) {
                unset($items[$key]['userId']);
            }
        }

        return $items;
    }

    private function getUserIds(array $items): array
    {
        $userIds = [];

        /** @var array{userId:int}[] $items */
        foreach ($items as $item) {
            $userIds[] = $item['userId'];
        }

        return array_unique($userIds);
    }
}
