<?php

declare(strict_types=1);

namespace App\Modules\Notifier\Service\Pusher;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class BadgeCommand
{
    public function __construct(
        #[Assert\NotBlank]
        public string $bundleId,
        #[Assert\NotBlank]
        public string $platform,
        #[Assert\NotBlank]
        public array $tokens,
        public int $badge,
    ) {}
}
