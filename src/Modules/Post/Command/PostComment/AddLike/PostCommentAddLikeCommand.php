<?php

declare(strict_types=1);

namespace App\Modules\Post\Command\PostComment\AddLike;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class PostCommentAddLikeCommand
{
    public function __construct(
        #[Assert\NotBlank]
        public int $userId,
        #[Assert\NotBlank]
        public int $commentId
    ) {}
}
