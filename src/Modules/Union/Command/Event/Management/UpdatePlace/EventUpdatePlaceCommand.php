<?php

declare(strict_types=1);

namespace App\Modules\Union\Command\Event\Management\UpdatePlace;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class EventUpdatePlaceCommand
{
    public function __construct(
        #[Assert\NotBlank]
        public int $userId,
        #[Assert\NotBlank]
        public int $unionId,
        #[Assert\NotBlank]
        public int $placeId,
    ) {}
}
