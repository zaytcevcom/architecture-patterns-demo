<?php

declare(strict_types=1);

namespace App\Modules\Notifier\Command\Post\PostLiked;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class PostLikedCommand
{
    public function __construct(
        #[Assert\NotBlank]
        public int $userId,
        #[Assert\NotBlank]
        public int $postId,
        #[Assert\NotBlank]
        public int $likeId,
    ) {}
}
