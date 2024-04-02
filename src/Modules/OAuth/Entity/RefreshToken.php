<?php

declare(strict_types=1);

namespace App\Modules\OAuth\Entity;

use Doctrine\ORM\Mapping as ORM;
use DomainException;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\RefreshTokenEntityInterface;
use League\OAuth2\Server\Entities\Traits\EntityTrait;
use League\OAuth2\Server\Entities\Traits\RefreshTokenTrait;

#[ORM\Entity]
#[ORM\Table(name: 'oauth_refresh_tokens')]
#[ORM\Index(fields: ['identifier'], name: 'IDX_SEARCH')]
#[ORM\Index(fields: ['userIdentifier'], name: 'IDX_USER_ID')]
#[ORM\Index(fields: ['pushToken'], name: 'IDX_PUSH_TOKEN')]
class RefreshToken implements RefreshTokenEntityInterface
{
    use EntityTrait;
    use RefreshTokenTrait;

    /** @psalm-suppress MissingPropertyType */
    #[ORM\Column(type: 'string', length: 80)]
    #[ORM\Id]
    protected $identifier;

    /** @psalm-suppress MissingPropertyType */
    #[ORM\Column(type: 'datetime_immutable')]
    protected $expiryDateTime;

    #[ORM\Column(type: 'integer', nullable: false)]
    private ?string $userIdentifier = null;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $locale = null;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $pushToken = null;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $voipToken = null;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $baseOS = null;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $buildId = null;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $brand = null;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $buildNumber = null;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $bundleId = null;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $carrier = null;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $deviceId = null;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $deviceName = null;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $ipAddress = null;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $installerPackageName = null;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $macAddress = null;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $manufacturer = null;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $model = null;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $systemName = null;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $systemVersion = null;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $userAgent = null;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $version = null;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $createdAt = 0;

    public function setAccessToken(AccessTokenEntityInterface $accessToken): void
    {
        $this->accessToken = $accessToken;
        $this->userIdentifier = (string)$accessToken->getUserIdentifier();
    }

    public function getUserIdentifier(): ?string
    {
        if (null === $this->userIdentifier) {
            throw new DomainException('Id not set');
        }

        return $this->userIdentifier;
    }

    public function getLocale(): ?string
    {
        return $this->locale;
    }

    public function setLocale(?string $locale): void
    {
        $this->locale = $locale;
    }

    public function getPushToken(): ?string
    {
        return $this->pushToken;
    }

    public function setPushToken(?string $pushToken = null): void
    {
        if (empty($pushToken)) {
            $pushToken = null;
        }

        $this->pushToken = $pushToken;
    }

    public function getVoipToken(): ?string
    {
        return $this->voipToken;
    }

    public function setVoipToken(?string $voipToken): void
    {
        $this->voipToken = $voipToken;
    }

    public function getBaseOS(): ?string
    {
        return $this->baseOS;
    }

    public function setBaseOS(?string $baseOS): void
    {
        $this->baseOS = $baseOS;
    }

    public function getBuildId(): ?string
    {
        return $this->buildId;
    }

    public function setBuildId(?string $buildId): void
    {
        $this->buildId = $buildId;
    }

    public function getBrand(): ?string
    {
        return $this->brand;
    }

    public function setBrand(?string $brand): void
    {
        $this->brand = $brand;
    }

    public function getBuildNumber(): ?string
    {
        return $this->buildNumber;
    }

    public function setBuildNumber(?string $buildNumber): void
    {
        $this->buildNumber = $buildNumber;
    }

    public function getBundleId(): ?string
    {
        return $this->bundleId;
    }

    public function setBundleId(?string $bundleId): void
    {
        $this->bundleId = $bundleId;
    }

    public function getCarrier(): ?string
    {
        return $this->carrier;
    }

    public function setCarrier(?string $carrier): void
    {
        $this->carrier = $carrier;
    }

    public function getDeviceId(): ?string
    {
        return $this->deviceId;
    }

    public function setDeviceId(?string $deviceId): void
    {
        $this->deviceId = $deviceId;
    }

    public function getDeviceName(): ?string
    {
        return $this->deviceName;
    }

    public function setDeviceName(?string $deviceName): void
    {
        $this->deviceName = $deviceName;
    }

    public function getIpAddress(): ?string
    {
        return $this->ipAddress;
    }

    public function setIpAddress(?string $ipAddress): void
    {
        $this->ipAddress = $ipAddress;
    }

    public function getInstallerPackageName(): ?string
    {
        return $this->installerPackageName;
    }

    public function setInstallerPackageName(?string $installerPackageName): void
    {
        $this->installerPackageName = $installerPackageName;
    }

    public function getMacAddress(): ?string
    {
        return $this->macAddress;
    }

    public function setMacAddress(?string $macAddress): void
    {
        $this->macAddress = $macAddress;
    }

    public function getManufacturer(): ?string
    {
        return $this->manufacturer;
    }

    public function setManufacturer(?string $manufacturer): void
    {
        $this->manufacturer = $manufacturer;
    }

    public function getModel(): ?string
    {
        return $this->model;
    }

    public function setModel(?string $model): void
    {
        $this->model = $model;
    }

    public function getSystemName(): ?string
    {
        return $this->systemName;
    }

    public function setSystemName(?string $systemName): void
    {
        $this->systemName = $systemName;
    }

    public function getSystemVersion(): ?string
    {
        return $this->systemVersion;
    }

    public function setSystemVersion(?string $systemVersion): void
    {
        $this->systemVersion = $systemVersion;
    }

    public function getUserAgent(): ?string
    {
        return $this->userAgent;
    }

    public function setUserAgent(?string $userAgent): void
    {
        $this->userAgent = $userAgent;
    }

    public function getVersion(): ?string
    {
        return $this->version;
    }

    public function setVersion(?string $version): void
    {
        $this->version = $version;
    }

    public function getCreatedAt(): int
    {
        return $this->createdAt;
    }

    public function setCreatedAt(int $createdAt): void
    {
        $this->createdAt = $createdAt;
    }
}
