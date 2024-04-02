<?php

declare(strict_types=1);

namespace App\Modules\Post\Command\Post\CloseComments;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class PostCloseCommentsCommand
{
    public function __construct(
        #[Assert\NotBlank]
        public int $postId,
        #[Assert\NotBlank]
        public int $userId,
        #[Assert\Choice([true, false])]
        public bool $closeComments,
    ) {}
}
