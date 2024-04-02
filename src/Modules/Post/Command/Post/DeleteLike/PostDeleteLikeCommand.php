<?php

declare(strict_types=1);

namespace App\Modules\Post\Command\Post\DeleteLike;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class PostDeleteLikeCommand
{
    public function __construct(
        #[Assert\NotBlank]
        public int $userId,
        #[Assert\NotBlank]
        public int $postId,
    ) {}
}
