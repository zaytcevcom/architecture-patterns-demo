<?php

declare(strict_types=1);

namespace App\Modules\Union\Command\Community\Management\Update;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class CommunityUpdateCommand
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
        #[Assert\NotBlank]
        public int $ageLimit,
        public ?string $description = null,
        public ?string $website = null,
        public ?string $status = null,
        public ?int $cityId = null,
    ) {}
}
