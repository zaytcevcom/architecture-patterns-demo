<?php

declare(strict_types=1);

namespace App\Modules\Audio\Command\Audio\Remove;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class AudioRemoveCommand
{
    public function __construct(
        #[Assert\NotBlank]
        public int $userId,
        #[Assert\NotBlank]
        public int $audioId,
    ) {}
}
