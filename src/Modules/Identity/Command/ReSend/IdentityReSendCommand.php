<?php

declare(strict_types=1);

namespace App\Modules\Identity\Command\ReSend;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class IdentityReSendCommand
{
    public function __construct(
        #[Assert\NotBlank]
        public string $uniqueId,
        public string $ipReal,
        public ?string $ipAddress,
    ) {}
}
