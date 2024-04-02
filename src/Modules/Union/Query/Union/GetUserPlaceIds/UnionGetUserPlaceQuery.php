<?php

declare(strict_types=1);

namespace App\Modules\Union\Query\Union\GetUserPlaceIds;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class UnionGetUserPlaceQuery
{
    public function __construct(
        #[Assert\NotBlank]
        public int $userId,
    ) {}
}
