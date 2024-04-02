<?php

declare(strict_types=1);

namespace App\Modules\Union\Query\UnionSphere\GetByCategoryIds;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class UnionSphereGetByCategoryIdsQuery
{
    public function __construct(
        #[Assert\NotBlank]
        public array $ids,
        public string $locale = 'en'
    ) {}
}
