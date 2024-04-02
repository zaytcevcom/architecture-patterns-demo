<?php

declare(strict_types=1);

namespace App\Modules\Post\Query\Post\GetById;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class PostGetByIdQuery
{
    public function __construct(
        #[Assert\NotBlank]
        public int $id
    ) {}
}
