<?php

declare(strict_types=1);

namespace App\Modules\Union\Query\Union\GetUserCommunityIds;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class UnionGetUserCommunityQuery
{
    public function __construct(
        #[Assert\NotBlank]
        public int $userId,
    ) {}
}
