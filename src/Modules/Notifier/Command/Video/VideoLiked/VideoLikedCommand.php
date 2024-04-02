<?php

declare(strict_types=1);

namespace App\Modules\Notifier\Command\Video\VideoLiked;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class VideoLikedCommand
{
    public function __construct(
        #[Assert\NotBlank]
        public int $userId,
        #[Assert\NotBlank]
        public int $videoId,
        #[Assert\NotBlank]
        public int $likeId,
    ) {}
}
