<?php

declare(strict_types=1);

namespace App\Http\Action\Unifier\Union;

use App\Http\Action\Unifier\User\UserUnifier;
use App\Modules\Union\Entity\Union\Union;
use App\Modules\Union\Helpers\Permissions\Role;
use ZayMedia\Shared\Http\Unifier\UnifierInterface;

final readonly class UnionMembersUnifier implements UnifierInterface
{
    public function __construct(
        private UserUnifier $userUnifier,
    ) {}

    public function unifyOne(?int $userId, ?array $item, array|string $fields = []): array
    {
        /** @var array{array} $result */
        $result = $this->unify($userId, (null !== $item) ? [$item] : [], $fields);
        return (isset($result[0])) ? $result[0] : [];
    }

    public function unify(?int $userId, array $items, array|string $fields = []): array
    {
        $result = [];

        /** @var array $item */
        foreach ($items as $item) {
            if ($item['role'] === Union::roleCreator()) {
                $role = Role::CREATOR;
            } elseif (\in_array($item['role'], [Union::roleAdmin(), Union::roleEditor(), Union::roleModerator()], true)) {
                $role = Role::ADMIN;
            } else {
                $role = Role::MEMBER;
            }

            $result[] = [
                'user'      => $this->userUnifier->unifyOne($userId, $item, $fields),
                'joinedAt'  => $item['time_join'] ?? null,
                'role'      => $role->value,
            ];
        }

        return $result;
    }
}
