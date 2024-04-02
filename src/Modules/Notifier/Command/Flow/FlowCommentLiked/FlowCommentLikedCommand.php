<?php

declare(strict_types=1);

namespace App\Modules\Notifier\Command\Flow\FlowCommentLiked;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class FlowCommentLikedCommand
{
    public function __construct(
        #[Assert\NotBlank]
        public int $userId,
        #[Assert\NotBlank]
        public int $commentId,
        #[Assert\NotBlank]
        public int $likeId,
    ) {}
}
