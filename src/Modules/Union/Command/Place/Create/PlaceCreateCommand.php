<?php

declare(strict_types=1);

namespace App\Modules\Union\Command\Place\Create;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class PlaceCreateCommand
{
    public function __construct(
        #[Assert\NotBlank]
        public int $creatorId,
        #[Assert\NotBlank]
        public string $name,
        #[Assert\NotBlank]
        public int $categoryId,
        #[Assert\NotBlank]
        public int $cityId,
        #[Assert\NotBlank]
        public string $address,
        #[Assert\NotBlank]
        public float $latitude,
        #[Assert\NotBlank]
        public float $longitude,
        #[Assert\NotBlank]
        public array $workingHours,
        public ?string $description = null,
        public ?string $website = null,
        public ?string $photoHost = null,
        public ?string $photoFileId = null,
    ) {}
}
