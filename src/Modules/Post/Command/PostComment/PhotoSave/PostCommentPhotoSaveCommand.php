<?php

declare(strict_types=1);

namespace App\Modules\Post\Command\PostComment\PhotoSave;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class PostCommentPhotoSaveCommand
{
    public function __construct(
        #[Assert\NotBlank]
        public int $userId,
        public ?int $unionId,
        #[Assert\NotBlank]
        public string $host,
        #[Assert\NotBlank]
        public string $fileId,
    ) {}
}
