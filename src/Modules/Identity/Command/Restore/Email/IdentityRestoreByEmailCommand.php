<?php

declare(strict_types=1);

namespace App\Modules\Identity\Command\Restore\Email;

use Symfony\Component\Validator\Constraints as Assert;
use ZayMedia\Shared\Components\Validator\Regex;

final class IdentityRestoreByEmailCommand
{
    #[Assert\Email]
    #[Assert\NotBlank]
    public string $email = '';

    #[Assert\Regex(pattern: Regex::FIRST_NAME)]
    public string $firstName = '';

    #[Assert\Regex(pattern: Regex::LAST_NAME)]
    public string $lastName = '';
}
