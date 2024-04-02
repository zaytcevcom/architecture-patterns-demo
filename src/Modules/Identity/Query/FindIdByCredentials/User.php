<?php

declare(strict_types=1);

namespace App\Modules\Identity\Query\FindIdByCredentials;

final readonly class User
{
    public function __construct(
        public int $id,
        public bool $isActive,
    ) {}
}
