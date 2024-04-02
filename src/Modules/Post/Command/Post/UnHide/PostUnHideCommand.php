<?php

declare(strict_types=1);

namespace App\Modules\Post\Command\Post\UnHide;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class PostUnHideCommand
{
    public function __construct(
        #[Assert\NotBlank]
        public int $postId,
        #[Assert\NotBlank]
        public int $userId,
    ) {}
}
