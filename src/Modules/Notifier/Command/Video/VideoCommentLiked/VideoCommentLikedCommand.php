<?php

declare(strict_types=1);

namespace App\Modules\Notifier\Command\Video\VideoCommentLiked;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class VideoCommentLikedCommand
{
    public function __construct(
        #[Assert\NotBlank]
        public int $userId,
        #[Assert\NotBlank]
        public int $commentId,
        #[Assert\NotBlank]
        public int $likeId,
    ) {}
}
