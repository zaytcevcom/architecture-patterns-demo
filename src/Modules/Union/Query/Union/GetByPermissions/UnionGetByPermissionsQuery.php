<?php

declare(strict_types=1);

namespace App\Modules\Union\Query\Union\GetByPermissions;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class UnionGetByPermissionsQuery
{
    public function __construct(
        #[Assert\NotBlank]
        public int $userId,
        #[Assert\NotBlank]
        public array|string $permissions,
        public int $count = 100,
        public int $offset = 0,
        public array|string $fields = ''
    ) {}
}
