<?php

declare(strict_types=1);

namespace App\Modules\Post\Command\PostComment\Delete;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class PostCommentDeleteCommand
{
    public function __construct(
        #[Assert\NotBlank]
        public int $userId,
        #[Assert\NotBlank]
        public int $commentId,
    ) {}
}
