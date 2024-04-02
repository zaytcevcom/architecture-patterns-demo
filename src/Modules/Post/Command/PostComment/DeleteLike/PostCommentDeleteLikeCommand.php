<?php

declare(strict_types=1);

namespace App\Modules\Post\Command\PostComment\DeleteLike;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class PostCommentDeleteLikeCommand
{
    public function __construct(
        #[Assert\NotBlank]
        public int $userId,
        #[Assert\NotBlank]
        public int $commentId,
    ) {}
}
