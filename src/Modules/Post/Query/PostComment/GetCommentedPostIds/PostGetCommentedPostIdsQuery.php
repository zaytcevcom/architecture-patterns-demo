<?php

declare(strict_types=1);

namespace App\Modules\Post\Query\PostComment\GetCommentedPostIds;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class PostGetCommentedPostIdsQuery
{
    public function __construct(
        #[Assert\NotBlank]
        public int $userId,
        #[Assert\NotBlank]
        public array $ids,
    ) {}
}
