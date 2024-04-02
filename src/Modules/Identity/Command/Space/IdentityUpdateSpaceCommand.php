<?php

declare(strict_types=1);

namespace App\Modules\Identity\Command\Space;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class IdentityUpdateSpaceCommand
{
    public function __construct(
        #[Assert\NotBlank]
        public int $userId,
        #[Assert\NotBlank]
        public int $spaceId,
    ) {}
}
