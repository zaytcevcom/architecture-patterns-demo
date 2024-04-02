<?php

declare(strict_types=1);

namespace App\Modules\Union\Query\Place\GetRecommendation;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class PlaceGetRecommendationQuery
{
    public function __construct(
        #[Assert\NotBlank]
        public int $spaceId,
        public int $sort = 0,
        public int $count = 100,
        public int $offset = 0,
        public array|string $fields = [],
    ) {}
}
