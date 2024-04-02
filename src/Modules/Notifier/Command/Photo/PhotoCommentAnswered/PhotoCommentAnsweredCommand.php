<?php

declare(strict_types=1);

namespace App\Modules\Notifier\Command\Photo\PhotoCommentAnswered;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class PhotoCommentAnsweredCommand
{
    public function __construct(
        #[Assert\NotBlank]
        public int $userId,
        #[Assert\NotBlank]
        public int $commentId,
    ) {}
}
