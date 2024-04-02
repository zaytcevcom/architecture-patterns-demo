<?php

declare(strict_types=1);

namespace App\Modules\Union\Command\Community\Create;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class CommunityCreateCommand
{
    public function __construct(
        #[Assert\NotBlank]
        public int $creatorId,
        #[Assert\NotBlank]
        public string $name,
        public ?int $categoryId = null,
        public ?int $cityId = null,
        public ?string $description = null,
        public ?string $website = null,
        public ?string $photoHost = null,
        public ?string $photoFileId = null,
    ) {}
}
