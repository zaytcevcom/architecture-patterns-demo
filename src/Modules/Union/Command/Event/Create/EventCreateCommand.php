<?php

declare(strict_types=1);

namespace App\Modules\Union\Command\Event\Create;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class EventCreateCommand
{
    public function __construct(
        #[Assert\NotBlank]
        public int $creatorId,
        #[Assert\NotBlank]
        public int $placeId,
        #[Assert\NotBlank]
        public string $name,
        #[Assert\NotBlank]
        public int $categoryId,
        #[Assert\NotBlank]
        public array $dates,
        public ?string $description = null,
        public ?string $photoHost = null,
        public ?string $photoFileId = null,
    ) {}
}
