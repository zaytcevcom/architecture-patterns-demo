<?php

declare(strict_types=1);

namespace App\Modules\Identity\Command\SignupByEmail\Confirm;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class IdentitySignupByEmailConfirmCommand
{
    public function __construct(
        #[Assert\NotBlank]
        public string $uniqueId,
        #[Assert\NotBlank]
        public string $code,
        #[Assert\NotBlank]
        public string $clientId,
    ) {}
}
