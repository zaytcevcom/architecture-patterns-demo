<?php

declare(strict_types=1);

namespace App\Modules\Identity\Command\UpdateStatus;

final readonly class IdentityUpdateStatusCommand
{
    public function __construct(
        public int $userId,
        public ?string $status = null
    ) {}
}
