<?php

declare(strict_types=1);

namespace App\Modules\Identity\Command\UpdateMain;

use Symfony\Component\Validator\Constraints as Assert;
use ZayMedia\Shared\Components\Validator\Regex;

final readonly class IdentityUpdateMainCommand
{
    public function __construct(
        public int $userId,
        #[Assert\Regex(pattern: Regex::FIRST_NAME)]
        public ?string $firstName = null,
        #[Assert\Regex(pattern: Regex::LAST_NAME)]
        public ?string $lastName = null,
        public ?string $birthday = null,
        public ?int $countryId = null,
        public ?int $cityId = null,
        public ?int $sex = null,
    ) {}
}
