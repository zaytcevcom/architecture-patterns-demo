<?php

declare(strict_types=1);

namespace App\Modules\Notifier\Command\Call;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class CallCommand
{
    public function __construct(
        #[Assert\NotBlank]
        public int $sourceId,
        #[Assert\NotBlank]
        public int $targetId,
        #[Assert\NotBlank]
        public int $callId,
        #[Assert\NotBlank]
        public string $roomId,
        #[Assert\NotBlank]
        public string $connection,
        #[Assert\NotBlank]
        public string $channel,
        #[Assert\NotBlank]
        public string $uuid,
        #[Assert\NotBlank]
        public string $stunHost,
        #[Assert\NotBlank]
        public string $turnHost,
        #[Assert\NotBlank]
        public string $turnLogin,
        #[Assert\NotBlank]
        public string $turnPassword,
        #[Assert\NotBlank]
        public string $stunHost2,
        #[Assert\NotBlank]
        public string $turnHost2,
        #[Assert\NotBlank]
        public string $turnLogin2,
        #[Assert\NotBlank]
        public string $turnPassword2,
    ) {}
}
