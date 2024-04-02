<?php

declare(strict_types=1);

namespace App\Modules\Union\Query\Union\GetByIds;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class UnionGetByIdsQuery
{
    public function __construct(
        #[Assert\NotBlank]
        public array|string $ids,
        public array|string $fields = ''
    ) {}
}
