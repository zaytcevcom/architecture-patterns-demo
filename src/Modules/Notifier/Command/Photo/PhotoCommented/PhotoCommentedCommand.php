<?php

declare(strict_types=1);

namespace App\Modules\Notifier\Command\Photo\PhotoCommented;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class PhotoCommentedCommand
{
    public function __construct(
        #[Assert\NotBlank]
        public int $userId,
        #[Assert\NotBlank]
        public int $photoId,
    ) {}
}
