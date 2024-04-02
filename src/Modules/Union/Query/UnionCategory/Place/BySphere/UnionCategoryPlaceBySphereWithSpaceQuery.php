<?php

declare(strict_types=1);

namespace App\Modules\Union\Query\UnionCategory\Place\BySphere;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class UnionCategoryPlaceBySphereWithSpaceQuery
{
    public function __construct(
        #[Assert\NotBlank]
        public int $spaceId,
        #[Assert\NotBlank]
        public int $sphereId,
        public ?string $search,
        public int $sort = 1,
        public int $count = 100,
        public int $offset = 0,
        public string $locale = 'en',
    ) {}
}
