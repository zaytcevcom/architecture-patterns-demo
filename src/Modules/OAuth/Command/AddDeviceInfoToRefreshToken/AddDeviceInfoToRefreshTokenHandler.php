<?php

declare(strict_types=1);

namespace App\Modules\OAuth\Command\AddDeviceInfoToRefreshToken;

use App\Modules\OAuth\Entity\RefreshTokenRepository;
use ZayMedia\Shared\Components\Flusher;

final readonly class AddDeviceInfoToRefreshTokenHandler
{
    public function __construct(
        private RefreshTokenRepository $refreshTokenRepository,
        private Flusher $flusher
    ) {}

    public function handle(AddDeviceInfoToRefreshTokenCommand $command): void
    {
        if ($command->identifier === null) {
            return;
        }

        $refreshToken = $this->refreshTokenRepository->findByIdentifier($command->identifier);

        if (empty($refreshToken)) {
            return;
        }

        $refreshToken->setLocale($command->locale);
        $refreshToken->setPushToken($command->pushToken);
        $refreshToken->setVoipToken($command->voipToken);
        $refreshToken->setBaseOS($command->baseOS);
        $refreshToken->setBuildId($command->buildId);
        $refreshToken->setBrand($command->brand);
        $refreshToken->setBuildNumber($command->buildNumber);
        $refreshToken->setBundleId($command->bundleId);
        $refreshToken->setCarrier($command->carrier);
        $refreshToken->setDeviceId($command->deviceId);
        $refreshToken->setDeviceName($command->deviceName);
        $refreshToken->setIpAddress($command->ipAddress);
        $refreshToken->setInstallerPackageName($command->installerPackageName);
        $refreshToken->setMacAddress($command->macAddress);
        $refreshToken->setManufacturer($command->manufacturer);
        $refreshToken->setModel($command->model);
        $refreshToken->setSystemName($command->systemName);
        $refreshToken->setSystemVersion($command->systemVersion);
        $refreshToken->setUserAgent($command->userAgent);
        $refreshToken->setVersion($command->version);

        //        $this->refreshTokenRepository->add($refreshToken);

        $this->flusher->flush();
    }
}
