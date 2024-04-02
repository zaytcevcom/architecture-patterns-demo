<?php

declare(strict_types=1);

namespace App\Modules\Post\Query\Feed\Get;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class FeedGetQuery
{
    public function __construct(
        #[Assert\NotBlank]
        public int $userId,
        public ?string $space = null,
        public ?string $content = null,
        public int $sort = 0,
        public int $count = 100,
        public ?string $cursor = null,
        public ?int $offset = null // todo: delete
    ) {}
}
