<?php

declare(strict_types=1);

namespace App\Modules\Post\Command\Post\Delete;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class PostDeleteCommand
{
    public function __construct(
        #[Assert\NotBlank]
        public int $userId,
        #[Assert\NotBlank]
        public int $postId,
    ) {}
}
