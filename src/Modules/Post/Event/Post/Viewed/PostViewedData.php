<?php

declare(strict_types=1);

namespace App\Modules\Post\Event\Post\Viewed;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class PostViewedData
{
    public function __construct(
        #[Assert\NotBlank]
        public int $userId,
        #[Assert\NotBlank]
        public int $postId,
    ) {}
}
