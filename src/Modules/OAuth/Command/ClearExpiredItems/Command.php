<?php

declare(strict_types=1);

namespace App\Modules\OAuth\Command\ClearExpiredItems;

final readonly class Command
{
    public function __construct(
        public string $date
    ) {}
}
