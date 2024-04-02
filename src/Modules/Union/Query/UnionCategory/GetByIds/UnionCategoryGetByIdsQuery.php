<?php

declare(strict_types=1);

namespace App\Modules\Union\Query\UnionCategory\GetByIds;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class UnionCategoryGetByIdsQuery
{
    public function __construct(
        #[Assert\NotBlank]
        public array $ids,
        public string $locale = 'en'
    ) {}
}
