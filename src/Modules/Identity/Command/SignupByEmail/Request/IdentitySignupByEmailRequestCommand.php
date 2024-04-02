<?php

declare(strict_types=1);

namespace App\Modules\Identity\Command\SignupByEmail\Request;

use Symfony\Component\Validator\Constraints as Assert;
use ZayMedia\Shared\Components\Validator\Regex;

final readonly class IdentitySignupByEmailRequestCommand
{
    public function __construct(
        #[Assert\Regex(pattern: Regex::FIRST_NAME)]
        public string $firstName,
        #[Assert\Regex(pattern: Regex::LAST_NAME)]
        public string $lastName,
        public ?int $sex,
        #[Assert\Length(min: 8)]
        #[Assert\NotBlank]
        public string $password,
        #[Assert\Email]
        #[Assert\NotBlank]
        public string $email,
        public ?string $photoHost = null,
        public ?string $photoFileId = null,

        // Device info

        public ?string $baseOS = null,
        public ?string $buildId = null,
        public ?string $brand = null,
        public ?string $buildNumber = null,
        public ?string $bundleId = null,
        public ?string $carrier = null,
        public ?string $deviceId = null,
        public ?string $deviceName = null,
        public ?string $ipAddress = null,
        public ?string $installerPackageName = null,
        public ?string $macAddress = null,
        public ?string $manufacturer = null,
        public ?string $model = null,
        public ?string $systemName = null,
        public ?string $systemVersion = null,
        public ?string $userAgent = null,
        public ?string $version = null,
    ) {}
}
