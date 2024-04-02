<?php

declare(strict_types=1);

namespace App\Modules\Notifier\Command\Flow\FlowLiked;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class FlowLikedCommand
{
    public function __construct(
        #[Assert\NotBlank]
        public int $userId,
        #[Assert\NotBlank]
        public int $flowId,
        #[Assert\NotBlank]
        public int $likeId,
    ) {}
}
