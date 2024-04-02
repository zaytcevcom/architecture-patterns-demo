<?php

declare(strict_types=1);

namespace App\Modules\Union\Query\UnionSphere\GetByIds;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class UnionSphereGetByIdsQuery
{
    public function __construct(
        #[Assert\NotBlank]
        public array $ids,
        public string $locale = 'en'
    ) {}
}
