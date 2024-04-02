<?php

declare(strict_types=1);

namespace App\Modules\Identity\Command\Location;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class IdentityUpdateLocationCommand
{
    public function __construct(
        #[Assert\NotBlank]
        public int $userId,
        #[Assert\NotBlank]
        public float $latitude,
        #[Assert\NotBlank]
        public float $longitude,
    ) {}
}
