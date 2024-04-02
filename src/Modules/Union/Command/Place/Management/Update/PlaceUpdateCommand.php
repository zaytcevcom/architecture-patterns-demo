<?php

declare(strict_types=1);

namespace App\Modules\Union\Command\Place\Management\Update;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class PlaceUpdateCommand
{
    public function __construct(
        #[Assert\NotBlank]
        public int $userId,
        #[Assert\NotBlank]
        public int $unionId,
        #[Assert\NotBlank]
        public string $name,
        #[Assert\NotBlank]
        public int $categoryId,
        public ?string $description = null,
        public ?string $website = null,
        public ?string $status = null,
    ) {}
}
