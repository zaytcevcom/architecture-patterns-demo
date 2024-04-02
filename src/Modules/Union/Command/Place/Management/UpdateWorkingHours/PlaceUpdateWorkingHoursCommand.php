<?php

declare(strict_types=1);

namespace App\Modules\Union\Command\Place\Management\UpdateWorkingHours;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class PlaceUpdateWorkingHoursCommand
{
    public function __construct(
        #[Assert\NotBlank]
        public int $userId,
        #[Assert\NotBlank]
        public int $unionId,
        #[Assert\NotBlank]
        public array $workingHours,
    ) {}
}
