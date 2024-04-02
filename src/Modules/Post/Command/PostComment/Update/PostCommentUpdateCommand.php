<?php

declare(strict_types=1);

namespace App\Modules\Post\Command\PostComment\Update;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class PostCommentUpdateCommand
{
    public function __construct(
        #[Assert\NotBlank]
        public int $userId,
        #[Assert\NotBlank]
        public int $commentId,
        public ?string $message,
        /** @var int[]|null $photoIds */
        public ?array $photoIds,
        /** @var int[]|null $audioIds */
        public ?array $audioIds,
        /** @var int[]|null $videoIds */
        public ?array $videoIds,
        public ?int $stickerId,
    ) {}
}
