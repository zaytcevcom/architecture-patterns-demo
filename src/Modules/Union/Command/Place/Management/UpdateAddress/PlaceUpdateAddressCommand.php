<?php

declare(strict_types=1);

namespace App\Modules\Union\Command\Place\Management\UpdateAddress;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class PlaceUpdateAddressCommand
{
    public function __construct(
        #[Assert\NotBlank]
        public int $userId,
        #[Assert\NotBlank]
        public int $unionId,
        #[Assert\NotBlank]
        public int $cityId,
        #[Assert\NotBlank]
        public string $address,
        #[Assert\NotBlank]
        public float $latitude,
        #[Assert\NotBlank]
        public float $longitude,
    ) {}
}
