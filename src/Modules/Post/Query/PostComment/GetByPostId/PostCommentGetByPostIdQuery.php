<?php

declare(strict_types=1);

namespace App\Modules\Post\Query\PostComment\GetByPostId;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class PostCommentGetByPostIdQuery
{
    public function __construct(
        #[Assert\NotBlank]
        public int $postId,
        public ?int $commentId = null,
        public int $sort = 0,
        public int $count = 100,
        public int $offset = 0,
    ) {}
}
