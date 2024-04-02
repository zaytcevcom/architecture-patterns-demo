<?php

declare(strict_types=1);

namespace App\Modules\Union\Query\Union\GetByEventIds;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class UnionGetByEventIdsQuery
{
    public function __construct(
        #[Assert\NotBlank]
        public array|string $ids,
        public array|string $fields = ''
    ) {}
}
