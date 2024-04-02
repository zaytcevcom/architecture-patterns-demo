<?php

declare(strict_types=1);

namespace App\Modules\Audio\Command\Audio\AudioListen;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class AudioListenCommand
{
    public function __construct(
        #[Assert\NotBlank]
        public int $audioId,
        #[Assert\NotBlank]
        public int $userId,
    ) {}
}
