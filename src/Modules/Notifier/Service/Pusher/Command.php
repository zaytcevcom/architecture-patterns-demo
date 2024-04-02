<?php

declare(strict_types=1);

namespace App\Modules\Notifier\Service\Pusher;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class Command
{
    public function __construct(
        #[Assert\NotBlank]
        public string $bundleId,
        #[Assert\NotBlank]
        public string $platform,
        #[Assert\NotBlank]
        public string $locale,
        #[Assert\NotBlank]
        public array $tokens,
        #[Assert\NotBlank]
        public string $title,
        #[Assert\NotBlank]
        public string $body,
        public ?string $subtitle = null,
        public ?string $category = null,
        public ?string $thread = null,
        public array $data = [],
        public ?int $badge = null,
        public ?string $sound = 'default',
        public array $translateParams = [],
    ) {}
}
