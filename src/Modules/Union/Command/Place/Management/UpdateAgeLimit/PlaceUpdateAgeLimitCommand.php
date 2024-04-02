<?php

declare(strict_types=1);

namespace App\Modules\Union\Command\Place\Management\UpdateAgeLimit;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class PlaceUpdateAgeLimitCommand
{
    public function __construct(
        #[Assert\NotBlank]
        public int $userId,
        #[Assert\NotBlank]
        public int $unionId,
        public int $ageLimit,
    ) {}
}
