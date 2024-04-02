<?php

declare(strict_types=1);

namespace App\Modules\Post\Query\Post\GetRepostedFlowIds;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class PostGetRepostedFlowIdsQuery
{
    public function __construct(
        #[Assert\NotBlank]
        public int $userId,
        #[Assert\NotBlank]
        public array $ids,
    ) {}
}
