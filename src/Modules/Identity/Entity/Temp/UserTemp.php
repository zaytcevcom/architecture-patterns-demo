<?php

declare(strict_types=1);

namespace App\Modules\Identity\Entity\Temp;

use App\Modules\Identity\Entity\Device\Device;
use App\Modules\Identity\Entity\User\Fields\Code;
use App\Modules\Identity\Entity\User\Fields\Email;
use App\Modules\Identity\Entity\User\Fields\FirstName;
use App\Modules\Identity\Entity\User\Fields\LastName;
use App\Modules\Identity\Entity\User\Fields\Phone;
use App\Modules\Identity\Entity\User\Fields\PhotoFileId;
use App\Modules\Identity\Entity\User\Fields\PhotoHost;
use App\Modules\Identity\Entity\User\Fields\Sex;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'identity_temp')]
class UserTemp
{
    public const TIME_INTERVAL = 3 * 60;

    #[ORM\Id]
    #[ORM\Column(type: 'integer', unique: true)]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', unique: true, nullable: true)]
    private ?string $uniqueId = null;

    #[ORM\Column(type: 'integer')]
    private int $time;

    #[ORM\Column(type: 'user_phone', unique: true, nullable: true)]
    private ?Phone $phone = null;

    #[ORM\Column(type: 'user_email', unique: true, nullable: true)]
    private ?Email $email = null;

    #[ORM\Column(type: 'user_firstname')]
    private FirstName $firstName;

    #[ORM\Column(type: 'user_lastname')]
    private LastName $lastName;

    #[ORM\Column(type: 'user_sex', nullable: true)]
    private ?Sex $sex;

    #[ORM\Column(type: 'user_photo_host', nullable: true)]
    private ?PhotoHost $photoHost;

    #[ORM\Column(type: 'user_photo_file_id', nullable: true)]
    private ?PhotoFileId $photoFileId;

    #[ORM\Column(type: 'string')]
    private string $password;

    #[ORM\Column(type: 'boolean')]
    private bool $captchaVerified;

    /** @psalm-suppress PropertyNotSetInConstructor */
    #[ORM\Column(type: 'identity_user_code')]
    private Code $code;

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
    private ?string $ipReal = null;

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

    private function __construct(
        FirstName $firstName,
        LastName $lastName,
        ?Sex $sex,
        PhotoHost $photoHost,
        PhotoFileId $photoFileId,
        string $password,
    ) {
        $this->time = time();
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->sex = $sex;
        $this->photoHost = $photoHost;
        $this->photoFileId = $photoFileId;
        $this->password = $password;
        $this->captchaVerified = false;
    }

    public static function SignupByEmail(
        Email $email,
        FirstName $firstName,
        LastName $lastName,
        ?Sex $sex,
        PhotoHost $photoHost,
        PhotoFileId $photoFileId,
        string $password,
        ?Device $device
    ): self {
        $user = new self(
            firstName: $firstName,
            lastName: $lastName,
            sex: $sex,
            photoHost: $photoHost,
            photoFileId: $photoFileId,
            password: $password
        );

        $user->email = $email;
        $user->phone = null;
        $user->generateCode();
        $user->setDeviceInfo($device);

        return $user;
    }

    public static function SignupByPhone(
        Phone $phone,
        FirstName $firstName,
        LastName $lastName,
        ?Sex $sex,
        PhotoHost $photoHost,
        PhotoFileId $photoFileId,
        string $password,
        ?Device $device
    ): self {
        $user = new self(
            firstName: $firstName,
            lastName: $lastName,
            sex: $sex,
            photoHost: $photoHost,
            photoFileId: $photoFileId,
            password: $password
        );

        $user->phone = $phone;
        $user->email = null;
        $user->generateCode();
        $user->setDeviceInfo($device);

        return $user;
    }

    public function generateCode(): void
    {
        $code = (string)rand(10000, 99999);

        $this->code = new Code($code);
    }

    public function isValidCode(string $code): bool
    {
        return $this->code->getValue() === $code;
    }

    public function getTimeInterval(): int
    {
        return self::TIME_INTERVAL;
    }

    public function isValidInterval(): bool
    {
        return time() - $this->time > $this->getTimeInterval();
    }

    public function getSignupMethod(): int
    {
        return (!empty($this->getPhone())) ? 1 : 0;
    }

    public function setDeviceInfo(?Device $device): void
    {
        if (null === $device) {
            return;
        }

        $this->setBaseOS($device->getBaseOS());
        $this->setBuildId($device->getBuildId());
        $this->setBrand($device->getBrand());
        $this->setBuildNumber($device->getBuildNumber());
        $this->setBundleId($device->getBundleId());
        $this->setCarrier($device->getCarrier());
        $this->setDeviceId($device->getDeviceId());
        $this->setDeviceName($device->getDeviceName());
        $this->setIpAddress($device->getIpAddress());
        $this->setIpReal($device->getIpReal());
        $this->setInstallerPackageName($device->getInstallerPackageName());
        $this->setMacAddress($device->getMacAddress());
        $this->setManufacturer($device->getManufacturer());
        $this->setModel($device->getModel());
        $this->setSystemName($device->getSystemName());
        $this->setSystemVersion($device->getSystemVersion());
        $this->setUserAgent($device->getUserAgent());
        $this->setVersion($device->getVersion());
    }

    public function getUniqueId(): ?string
    {
        return $this->uniqueId;
    }

    public function setUniqueId(string $uniqueId): void
    {
        $this->uniqueId = $uniqueId;
    }

    public function getTime(): int
    {
        return $this->time;
    }

    public function setTime(int $time): void
    {
        $this->time = $time;
    }

    public function getPhone(): ?Phone
    {
        return $this->phone;
    }

    public function getEmail(): ?Email
    {
        return $this->email;
    }

    public function getFirstName(): FirstName
    {
        return $this->firstName;
    }

    public function setFirstName(FirstName $firstName): void
    {
        $this->firstName = $firstName;
    }

    public function getLastName(): LastName
    {
        return $this->lastName;
    }

    public function setLastName(LastName $lastName): void
    {
        $this->lastName = $lastName;
    }

    public function getSex(): ?Sex
    {
        return $this->sex;
    }

    public function setSex(?Sex $sex): void
    {
        $this->sex = $sex;
    }

    public function getPhotoHost(): ?PhotoHost
    {
        return $this->photoHost;
    }

    public function setPhotoHost(?PhotoHost $photoHost): void
    {
        $this->photoHost = $photoHost;
    }

    public function getPhotoFileId(): ?PhotoFileId
    {
        return $this->photoFileId;
    }

    public function setPhotoFileId(?PhotoFileId $photoFileId): void
    {
        $this->photoFileId = $photoFileId;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    public function isCaptchaVerified(): bool
    {
        return $this->captchaVerified;
    }

    public function captchaVerify(): void
    {
        $this->captchaVerified = true;
    }

    public function resetCaptchaVerified(): void
    {
        $this->captchaVerified = false;
    }

    public function getCode(): Code
    {
        return $this->code;
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
