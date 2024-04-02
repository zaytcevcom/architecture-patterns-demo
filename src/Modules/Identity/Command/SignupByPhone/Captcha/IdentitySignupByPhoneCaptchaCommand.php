<?php

declare(strict_types=1);

namespace App\Modules\Identity\Command\SignupByPhone\Captcha;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class IdentitySignupByPhoneCaptchaCommand
{
    public function __construct(
        #[Assert\NotBlank]
        public string $uniqueId,
        #[Assert\NotBlank]
        public string $code,
    ) {}
}
