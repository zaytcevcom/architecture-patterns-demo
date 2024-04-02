<?php

declare(strict_types=1);

namespace App\Modules\Post\Query\Post\GetUserHideIds;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class PostGetUserHideIdsQuery
{
    public function __construct(
        #[Assert\NotBlank]
        public int $userId,
    ) {}
}
