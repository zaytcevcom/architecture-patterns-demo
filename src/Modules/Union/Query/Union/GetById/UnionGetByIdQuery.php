<?php

declare(strict_types=1);

namespace App\Modules\Union\Query\Union\GetById;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class UnionGetByIdQuery
{
    public function __construct(
        #[Assert\NotBlank]
        public int $id,
        public array|string $fields = [],
    ) {}
}
