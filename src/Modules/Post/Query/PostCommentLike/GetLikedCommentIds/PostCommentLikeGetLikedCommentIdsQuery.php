<?php

declare(strict_types=1);

namespace App\Modules\Post\Query\PostCommentLike\GetLikedCommentIds;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class PostCommentLikeGetLikedCommentIdsQuery
{
    public function __construct(
        #[Assert\NotBlank]
        public ?int $userId,
        #[Assert\NotBlank]
        public array $ids,
    ) {}
}
