<?php

declare(strict_types=1);

namespace App\Modules\Union\Command\Event\Management\UpdateAgeLimit;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class EventUpdateAgeLimitCommand
{
    public function __construct(
        #[Assert\NotBlank]
        public int $userId,
        #[Assert\NotBlank]
        public int $unionId,
        public int $ageLimit,
    ) {}
}
