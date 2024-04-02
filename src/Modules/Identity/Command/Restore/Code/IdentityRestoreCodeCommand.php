<?php

declare(strict_types=1);

namespace App\Modules\Identity\Command\Restore\Code;

use Symfony\Component\Validator\Constraints as Assert;

final class IdentityRestoreCodeCommand
{
    #[Assert\NotBlank]
    public string $uniqueId = '';
    #[Assert\NotBlank]
    public string $ipReal = '';
    #[Assert\NotBlank]
    public string $ipAddress = '';
}
