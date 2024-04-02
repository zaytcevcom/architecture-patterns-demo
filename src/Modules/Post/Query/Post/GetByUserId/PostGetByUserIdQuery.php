<?php

declare(strict_types=1);

namespace App\Modules\Post\Query\Post\GetByUserId;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class PostGetByUserIdQuery
{
    public function __construct(
        #[Assert\NotBlank]
        public int $userId,
        public int $sort = 0,
        public int $count = 100,
        public ?string $cursor = null,
        public ?int $offset = null // todo: delete
    ) {}
}
