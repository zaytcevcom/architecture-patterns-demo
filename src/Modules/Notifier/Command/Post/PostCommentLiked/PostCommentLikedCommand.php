<?php

declare(strict_types=1);

namespace App\Modules\Notifier\Command\Post\PostCommentLiked;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class PostCommentLikedCommand
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
