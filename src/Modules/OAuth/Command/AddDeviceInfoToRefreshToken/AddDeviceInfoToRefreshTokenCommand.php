<?php

declare(strict_types=1);

namespace App\Modules\OAuth\Command\AddDeviceInfoToRefreshToken;

final readonly class AddDeviceInfoToRefreshTokenCommand
{
    public function __construct(
        public ?string $identifier = null,
        public ?string $locale = null,
        public ?string $pushToken = null,
        public ?string $voipToken = null,
        public ?string $baseOS = null,
        public ?string $buildId = null,
        public ?string $brand = null,
        public ?string $buildNumber = null,
        public ?string $bundleId = null,
        public ?string $carrier = null,
        public ?string $deviceId = null,
        public ?string $deviceName = null,
        public ?string $ipReal = null,
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
