<?php

declare(strict_types=1);

namespace App\Modules\Post\Command\Post\AddLike;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class PostAddLikeCommand
{
    public function __construct(
        #[Assert\NotBlank]
        public int $userId,
        #[Assert\NotBlank]
        public int $postId,
    ) {}
}
