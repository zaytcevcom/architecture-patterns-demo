<?php

declare(strict_types=1);

namespace App\Modules\Post\Service;

use App\Modules\Permissions\Service\UnionPermission;
use App\Modules\Post\Entity\Post\Post;

final class PostPermissions
{
    public function __construct(
        //        private readonly Realtime $realtime
    ) {}

    public function isAccess(UnionPermission $permission, int $userId, Post $post): bool
    {
        if ($post->getUserId() === $userId) {
            return true;
        }

        if ($unionId = $post->getUnionId()) {
            return $this->checkUnionPermissions($permission, $userId, $unionId);
        }

        return $this->checkUserPermissions($permission, $userId, $post->getUserId());
    }

    public function checkUserPermissions(UnionPermission $permission, int $userId, int $targetId): bool
    {
        if ($permission === UnionPermission::POSTS_READ) {
            return $this->canReadUserPosts($userId, $targetId);
        }

        return false;
    }

    public function checkUnionPermissions(UnionPermission $permission, int $userId, int $unionId): bool
    {
        if ($permission === UnionPermission::POSTS_READ) {
            return $this->canReadUnionPosts($userId, $unionId);
        }

        return false;
    }

    public function canReadUserPosts(int $userId, int $targetId): bool
    {
        // todo: наличие в контактах, чс

        return true;
    }

    public function canReadUnionPosts(int $userId, int $unionId): bool
    {
        // todo: наличие в объединении

        return true;
    }
}
