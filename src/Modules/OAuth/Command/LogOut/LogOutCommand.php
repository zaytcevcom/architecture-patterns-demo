<?php

declare(strict_types=1);

namespace App\Modules\OAuth\Command\LogOut;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class LogOutCommand
{
    public function __construct(
        #[Assert\NotBlank]
        public string $refreshToken
    ) {}
}
