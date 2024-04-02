<?php

declare(strict_types=1);

namespace App\Modules\Notifier\Command\Video\VideoCommented;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class VideoCommentedCommand
{
    public function __construct(
        #[Assert\NotBlank]
        public int $userId,
        #[Assert\NotBlank]
        public int $videoId,
    ) {}
}
