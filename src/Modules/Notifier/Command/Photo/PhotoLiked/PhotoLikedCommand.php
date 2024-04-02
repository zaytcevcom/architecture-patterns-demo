<?php

declare(strict_types=1);

namespace App\Modules\Notifier\Command\Photo\PhotoLiked;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class PhotoLikedCommand
{
    public function __construct(
        #[Assert\NotBlank]
        public int $userId,
        #[Assert\NotBlank]
        public int $photoId,
        #[Assert\NotBlank]
        public int $likeId,
    ) {}
}
