<?php

declare(strict_types=1);

namespace App\Modules\Identity\Command\Delete;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class IdentityDeleteCommand
{
    public function __construct(
        #[Assert\NotBlank]
        public int $userId,
    ) {}
}
