<?php

declare(strict_types=1);

namespace App\Modules\Post\Command\Post\Create;

use Symfony\Component\Validator\Constraints as Assert;

final class PostCreateCommand
{
    public function __construct(
        #[Assert\NotBlank]
        public readonly int $userId,
        public readonly ?int $unionId,
        public readonly ?string $message,
        /** @var int[]|null $photoIds */
        public ?array $photoIds,
        /** @var int[]|null $audioIds */
        public ?array $audioIds,
        /** @var int[]|null $videoIds */
        public ?array $videoIds,
        public readonly ?int $flowId,
        public readonly ?int $time,
        public readonly ?int $uniqueTime,
        public readonly bool $closeComments = false,
        public readonly bool $contactsOnly = false,
        /** @var int[]|null $socialIds */
        public ?array $socialIds = null,
        public readonly ?int $postedAt = null,
    ) {}
}
