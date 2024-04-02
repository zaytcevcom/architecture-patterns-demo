<?php

declare(strict_types=1);

namespace App\Modules\Post\Command\PostComment\Restore;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class PostCommentRestoreCommand
{
    public function __construct(
        #[Assert\NotBlank]
        public int $userId,
        #[Assert\NotBlank]
        public int $commentId,
    ) {}
}
