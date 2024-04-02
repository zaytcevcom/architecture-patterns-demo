<?php

declare(strict_types=1);

namespace App\Modules\Identity\Command\Restore\Phone;

use Symfony\Component\Validator\Constraints as Assert;
use ZayMedia\Shared\Components\Validator\Regex;

final class IdentityRestorePhoneCommand
{
    #[Assert\NotBlank]
    public string $phone = '';

    #[Assert\Regex(pattern: Regex::FIRST_NAME)]
    public string $firstName = '';

    #[Assert\Regex(pattern: Regex::LAST_NAME)]
    public string $lastName = '';
}
