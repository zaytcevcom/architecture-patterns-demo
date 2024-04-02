<?php

declare(strict_types=1);

namespace App\Modules\Identity\Service\Typesense\User;

class UserQuery
{
    public function __construct(
        public string $search,
        public ?int $countryId = null,
        public ?int $cityId = null,
        public ?int $marital = null,
        public ?int $sex = null,
        public ?int $ageFrom = null,
        public ?int $ageTo = null,
        public int $limit = 150
    ) {}
}
