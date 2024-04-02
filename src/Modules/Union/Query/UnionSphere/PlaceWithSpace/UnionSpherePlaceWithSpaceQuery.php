<?php

declare(strict_types=1);

namespace App\Modules\Union\Query\UnionSphere\PlaceWithSpace;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class UnionSpherePlaceWithSpaceQuery
{
    public function __construct(
        #[Assert\NotBlank]
        public int $spaceId,
        public ?string $search,
        public int $sort = 1,
        public int $count = 100,
        public int $offset = 0,
        public string $locale = 'en',
    ) {}
}
