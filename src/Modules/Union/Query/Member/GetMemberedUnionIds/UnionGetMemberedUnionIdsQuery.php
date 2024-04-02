<?php

declare(strict_types=1);

namespace App\Modules\Union\Query\Member\GetMemberedUnionIds;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class UnionGetMemberedUnionIdsQuery
{
    public function __construct(
        #[Assert\NotBlank]
        public ?int $userId,
        #[Assert\NotBlank]
        public array $ids,
    ) {}
}
