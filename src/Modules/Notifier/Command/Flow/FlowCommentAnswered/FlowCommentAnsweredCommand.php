<?php

declare(strict_types=1);

namespace App\Modules\Notifier\Command\Flow\FlowCommentAnswered;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class FlowCommentAnsweredCommand
{
    public function __construct(
        #[Assert\NotBlank]
        public int $userId,
        #[Assert\NotBlank]
        public int $commentId,
    ) {}
}
