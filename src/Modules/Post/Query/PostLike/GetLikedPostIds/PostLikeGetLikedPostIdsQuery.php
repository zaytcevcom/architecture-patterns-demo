<?php

declare(strict_types=1);

namespace App\Modules\Post\Query\PostLike\GetLikedPostIds;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class PostLikeGetLikedPostIdsQuery
{
    public function __construct(
        #[Assert\NotBlank]
        public ?int $userId,
        #[Assert\NotBlank]
        public array $ids,
    ) {}
}
