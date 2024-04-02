<?php

declare(strict_types=1);

namespace App\Modules\Post\Command\Post\Update;

use Symfony\Component\Validator\Constraints as Assert;

final class PostUpdateCommand
{
    public function __construct(
        #[Assert\NotBlank]
        public readonly int $userId,
        #[Assert\NotBlank]
        public readonly int $postId,
        public readonly ?string $message,
        /** @var int[]|null $photoIds */
        public ?array $photoIds,
        /** @var int[]|null $audioIds */
        public ?array $audioIds,
        /** @var int[]|null $videoIds */
        public ?array $videoIds,
        public readonly ?int $time,
        public readonly bool $closeComments,
        public readonly bool $contactsOnly,
    ) {}
}
