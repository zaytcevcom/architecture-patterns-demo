<?php

declare(strict_types=1);

namespace App\Modules\Post\Command\Post\View;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class PostViewCommand
{
    public function __construct(
        #[Assert\NotBlank]
        public int $postId,
        #[Assert\NotBlank]
        public int $userId,
    ) {}
}
