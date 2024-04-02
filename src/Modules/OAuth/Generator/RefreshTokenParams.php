<?php

declare(strict_types=1);

namespace App\Modules\OAuth\Generator;

use DateTimeImmutable;

final class RefreshTokenParams
{
    public function __construct(
        public DateTimeImmutable $expires,
    ) {}
}
