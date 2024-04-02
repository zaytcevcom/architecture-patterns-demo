<?php

declare(strict_types=1);

namespace App\Modules\Identity\Command\Restore\CodeConfirm;

use Symfony\Component\Validator\Constraints as Assert;

final class IdentityRestoreCodeConfirmCommand
{
    #[Assert\NotBlank]
    public string $uniqueId = '';

    #[Assert\NotBlank]
    public string $code = '';
}
