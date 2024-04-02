<?php

declare(strict_types=1);

namespace App\Modules\Identity\Query\Search;

final readonly class IdentitySearchQuery
{
    public function __construct(
        public string $search = '',
        public ?int $countryId = null,
        public ?int $cityId = null,
        public ?int $marital = null,
        public ?int $sex = null,
        public ?int $ageFrom = null,
        public ?int $ageTo = null,
        public int $count = 100,
        public int $offset = 0,
        public array|string $fields = '',
    ) {}
}
