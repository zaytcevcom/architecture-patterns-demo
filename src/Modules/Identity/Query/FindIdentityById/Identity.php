<?php

declare(strict_types=1);

namespace App\Modules\Identity\Query\FindIdentityById;

final readonly class Identity
{
    public function __construct(
        public string $id,
        public string $role
    ) {}
}
