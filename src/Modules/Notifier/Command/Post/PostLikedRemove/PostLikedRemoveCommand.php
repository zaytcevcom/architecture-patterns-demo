<?php

declare(strict_types=1);

namespace App\Modules\Notifier\Command\Post\PostLikedRemove;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class PostLikedRemoveCommand
{
    public function __construct(
        #[Assert\NotBlank]
        public int $userId,
        #[Assert\NotBlank]
        public int $likeId,
    ) {}
}
