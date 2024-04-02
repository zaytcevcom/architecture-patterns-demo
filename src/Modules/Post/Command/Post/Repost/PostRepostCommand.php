<?php

declare(strict_types=1);

namespace App\Modules\Post\Command\Post\Repost;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class PostRepostCommand
{
    public function __construct(
        #[Assert\NotBlank]
        public int $userId,
        public ?int $unionId,
        #[Assert\NotBlank]
        public int $postId,
        public ?string $message,
        public ?int $time,
        public ?int $uniqueTime,
        public bool $closeComments = false,
        public bool $contactsOnly = false,
    ) {}
}
