<?php

declare(strict_types=1);

namespace App\Modules\Notifier\Command\Video\VideoCommentAnswered;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class VideoCommentAnsweredCommand
{
    public function __construct(
        #[Assert\NotBlank]
        public int $userId,
        #[Assert\NotBlank]
        public int $commentId,
    ) {}
}
