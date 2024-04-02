<?php

declare(strict_types=1);

namespace App\Modules\Post\Query\Post\GetByIds;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class PostGetByIdsQuery
{
    public function __construct(
        #[Assert\NotBlank]
        public array $ids
    ) {}
}
