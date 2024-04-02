<?php

declare(strict_types=1);

namespace App\Modules\Union\Command\Community\CreateMusical;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class CommunityCreateMusicalCommand
{
    public function __construct(
        #[Assert\NotBlank]
        public int $creatorId,
        #[Assert\NotBlank]
        public string $name,
        public ?int $categoryId = null,
        public ?string $description = null,
        public ?string $website = null,
        public ?string $photoHost = null,
        public ?string $photoFileId = null,
    ) {}
}
