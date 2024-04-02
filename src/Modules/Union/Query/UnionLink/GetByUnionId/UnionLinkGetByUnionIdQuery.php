<?php

declare(strict_types=1);

namespace App\Modules\Union\Query\UnionLink\GetByUnionId;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class UnionLinkGetByUnionIdQuery
{
    public function __construct(
        #[Assert\NotBlank]
        public int $unionId,
        public int $sort = 0,
        public int $count = 100,
        public int $offset = 0,
    ) {}
}
