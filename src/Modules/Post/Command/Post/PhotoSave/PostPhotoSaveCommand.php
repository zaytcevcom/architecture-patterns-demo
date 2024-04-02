<?php

declare(strict_types=1);

namespace App\Modules\Post\Command\Post\PhotoSave;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class PostPhotoSaveCommand
{
    public function __construct(
        #[Assert\NotBlank]
        public int $userId,
        public ?int $unionId,
        #[Assert\NotBlank]
        public string $host,
        #[Assert\NotBlank]
        public string $fileId,
        public ?int $postedAt = null,
    ) {}
}
