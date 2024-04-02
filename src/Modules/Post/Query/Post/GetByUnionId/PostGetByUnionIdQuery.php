<?php

declare(strict_types=1);

namespace App\Modules\Post\Query\Post\GetByUnionId;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class PostGetByUnionIdQuery
{
    public function __construct(
        #[Assert\NotBlank]
        public int $unionId,
        public int $sort = 0,
        public int $count = 100,
        public ?string $cursor = null,
        public ?int $offset = null // todo: delete
    ) {}
}
