<?php

declare(strict_types=1);

namespace App\Modules\Identity\Command\SignupByPhone\Confirm;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class IdentitySignupByPhoneConfirmCommand
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
