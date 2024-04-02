<?php

declare(strict_types=1);

namespace App\Modules\Notifier\Command\Badge;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class BadgeCommand
{
    public function __construct(
        #[Assert\NotBlank]
        public int $userId,
    ) {}
}
