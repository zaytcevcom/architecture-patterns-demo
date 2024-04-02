<?php

declare(strict_types=1);

namespace App\Modules\Identity\Command\UpdatePersonal;

final readonly class IdentityUpdatePersonalCommand
{
    public function __construct(
        public int $userId,
        public ?int $marital = null,
        public ?int $maritalId = null,
        public ?int $contactCountryId = null,
        public ?int $contactCityId = null,
        public ?string $contactPhone = null,
        public ?string $contactSite = null,
    ) {}
}
