<?php

declare(strict_types=1);

namespace App\Modules\Identity\Command\Restore\Password;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class IdentityRestorePasswordCommand
{
    public function __construct(
        #[Assert\NotBlank]
        public string $uniqueId,
        #[Assert\NotBlank]
        public string $password,
    ) {}
}
