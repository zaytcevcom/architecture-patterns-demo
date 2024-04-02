<?php

declare(strict_types=1);

namespace App\Modules\Identity\Service\Typesense\User;

readonly class UserDocument
{
    public function __construct(
        public int $identifier,
        public string $title,
        public ?int $countryId,
        public ?int $cityId,
        public ?int $marital,
        public ?int $sex,
        public ?int $birthdayYear,
    ) {}
}
