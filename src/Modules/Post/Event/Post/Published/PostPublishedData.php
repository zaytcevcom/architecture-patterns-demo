<?php

declare(strict_types=1);

namespace App\Modules\Post\Event\Post\Published;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class PostPublishedData
{
    public function __construct(
        #[Assert\NotBlank]
        public int $userId,
        #[Assert\NotBlank]
        public int $postId,
        public array $socialIds
    ) {}
}
