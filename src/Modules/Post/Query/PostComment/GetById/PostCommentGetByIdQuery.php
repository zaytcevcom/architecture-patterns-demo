<?php

declare(strict_types=1);

namespace App\Modules\Post\Query\PostComment\GetById;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class PostCommentGetByIdQuery
{
    public function __construct(
        #[Assert\NotBlank]
        public int $id
    ) {}
}
