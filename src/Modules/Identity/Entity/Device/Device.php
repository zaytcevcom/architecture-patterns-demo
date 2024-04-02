<?php

declare(strict_types=1);

namespace App\Modules\Identity\Entity\Device;

use App\Modules\Identity\Entity\Temp\UserTemp;
use App\Modules\Identity\Entity\User\User;
use Doctrine\ORM\Mapping as ORM;
use DomainException;

#[ORM\Entity]
#[ORM\Table(name: 'identity_signup_devices')]
class Device
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer', unique: true)]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', unique: false, nullable: true)]
    private ?User $user;

    #[ORM\Column(type: 'integer')]
    private int $method;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $baseOS;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $buildId;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $brand;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $buildNumber;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $bundleId;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $carrier;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $deviceId;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $deviceName;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $ipAddress;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $ipReal;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $installerPackageName;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $macAddress;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $manufacturer;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $model;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $systemName;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $systemVersion;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $userAgent;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $version;

    public function __construct(
        ?User $user,
        int $method,
        ?string $baseOS = null,
        ?string $buildId = null,
        ?string $brand = null,
        ?string $buildNumber = null,
        ?string $bundleId = null,
        ?string $carrier = null,
        ?string $deviceId = null,
        ?string $deviceName = null,
        ?string $ipAddress = null,
        ?string $ipReal = null,
        ?string $installerPackageName = null,
        ?string $macAddress = null,
        ?string $manufacturer = null,
        ?string $model = null,
        ?string $systemName = null,
        ?string $systemVersion = null,
        ?string $userAgent = null,
        ?string $version = null
    ) {
        $this->user = $user;
        $this->method = $method;
        $this->baseOS = $baseOS;
        $this->buildId = $buildId;
        $this->brand = $brand;
        $this->buildNumber = $buildNumber;
        $this->bundleId = $bundleId;
        $this->carrier = $carrier;
        $this->deviceId = $deviceId;
        $this->deviceName = $deviceName;
        $this->ipAddress = $ipAddress;
        $this->ipReal = $ipReal;
        $this->installerPackageName = $installerPackageName;
        $this->macAddress = $macAddress;
        $this->manufacturer = $manufacturer;
        $this->model = $model;
        $this->systemName = $systemName;
        $this->systemVersion = $systemVersion;
        $this->userAgent = $userAgent;
        $this->version = $version;
    }

    public static function createFromTemp(User $user, UserTemp $userTemp): self
    {
        return new self(
            user: $user,
            method: $userTemp->getSignupMethod(),
            baseOS: $userTemp->getBaseOS(),
            buildId: $userTemp->getBuildId(),
            brand: $userTemp->getBrand(),
            buildNumber: $userTemp->getBuildNumber(),
            bundleId: $userTemp->getBundleId(),
            carrier: $userTemp->getCarrier(),
            deviceId: $userTemp->getDeviceId(),
            deviceName: $userTemp->getDeviceName(),
            ipAddress: $userTemp->getIpAddress(),
            ipReal: $userTemp->getIpReal(),
            installerPackageName: $userTemp->getInstallerPackageName(),
            macAddress: $userTemp->getMacAddress(),
            manufacturer: $userTemp->getManufacturer(),
            model: $userTemp->getModel(),
            systemName: $userTemp->getSystemName(),
            systemVersion: $userTemp->getSystemVersion(),
            userAgent: $userTemp->getUserAgent(),
            version: $userTemp->getVersion(),
        );
    }

    public function getId(): int
    {
        if (null === $this->id) {
            throw new DomainException('Id not set');
        }
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): void
    {
        $this->user = $user;
    }

    public function getMethod(): int
    {
        return $this->method;
    }

    public function setMethod(int $method): void
    {
        $this->method = $method;
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

    public function getIpReal(): ?string
    {
        return $this->ipReal;
    }

    public function setIpReal(?string $ipReal): void
    {
        $this->ipReal = $ipReal;
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
}
