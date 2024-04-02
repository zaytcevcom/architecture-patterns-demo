<?php

declare(strict_types=1);

namespace App\Modules\Union\Entity\UnionPlaceInfo;

use Doctrine\ORM\Mapping as ORM;
use DomainException;

#[ORM\Entity]
#[ORM\Table(name: 'unions_places')]
#[ORM\Index(fields: ['unionId'], name: 'union_id')]
class UnionPlaceInfo
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer', unique: true)]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private ?int $id = null;

    #[ORM\Column(type: 'integer')]
    private int $unionId;

    #[ORM\Column(type: 'string', length: 500, nullable: true)]
    private ?string $location;

    #[ORM\Column(type: 'string', length: 200, nullable: true)]
    private ?string $geolocation;

    #[ORM\Column(type: 'string', length: 500)]
    private string $workingHours;

    #[ORM\Column(type: 'string', length: 500, nullable: true)]
    private ?string $email;

    #[ORM\Column(type: 'string', length: 500, nullable: true)]
    private ?string $phone;

    #[ORM\Column(type: 'string', length: 500, nullable: true)]
    private ?string $phoneDescription;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $isRepresentative;

    #[ORM\Column(type: 'string', length: 500, nullable: true)]
    private ?string $locationObj;

    #[ORM\Column(type: 'string', length: 500, nullable: true)]
    private ?string $phones;

    #[ORM\Column(type: 'string', length: 500, nullable: true)]
    private ?string $features;

    #[ORM\Column(type: 'string', length: 500, nullable: true)]
    private ?string $contactPhone;

    #[ORM\Column(type: 'string', length: 500, nullable: true)]
    private ?string $contactEmail;

    #[ORM\Column(type: 'string', length: 500, nullable: true)]
    private ?string $contactPosition;

    private function __construct(
        int $unionId,
        ?string $location,
        string $workingHours,
        ?string $email,
        ?string $phone,
        ?string $phoneDescription,
    ) {
        $this->unionId = $unionId;
        $this->location = $location;
        $this->geolocation = null;
        $this->workingHours = $workingHours;
        $this->email = $email;
        $this->phone = $phone;
        $this->phoneDescription = $phoneDescription;

        $this->isRepresentative = 1;
        $this->locationObj = null;
        $this->phones = null;
        $this->features = null;
        $this->contactPhone = null;
        $this->contactEmail = null;
        $this->contactPosition = null;
    }

    public static function create(
        int $unionId,
        ?string $location,
        ?float $latitude,
        ?float $longitude,
        array $workingHours,
        ?string $email,
        ?string $phone,
        ?string $phoneDescription,
    ): self {
        $unionPlaceInfo = new self(
            unionId: $unionId,
            location: $location,
            workingHours: json_encode($workingHours),
            email: $email,
            phone: $phone,
            phoneDescription: $phoneDescription,
        );

        $unionPlaceInfo->setGeolocation($latitude, $longitude);

        return $unionPlaceInfo;
    }

    public function getId(): int
    {
        if (null === $this->id) {
            throw new DomainException('Id not set');
        }
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getUnionId(): int
    {
        return $this->unionId;
    }

    public function setUnionId(int $unionId): void
    {
        $this->unionId = $unionId;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(?string $location): void
    {
        $this->location = $location;
    }

    public function getGeolocation(): ?string
    {
        return $this->geolocation;
    }

    public function setGeolocation(?float $latitude, ?float $longitude): void
    {
        if (null === $latitude || null === $longitude) {
            $this->geolocation = null;
            return;
        }

        $this->geolocation = $latitude . ',' . $longitude;
    }

    public function getWorkingHours(): string
    {
        return $this->workingHours;
    }

    public function setWorkingHours(array $workingHours): void
    {
        $this->workingHours = json_encode($workingHours);
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): void
    {
        $this->email = $email;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): void
    {
        $this->phone = $phone;
    }

    public function getPhoneDescription(): ?string
    {
        return $this->phoneDescription;
    }

    public function setPhoneDescription(?string $phoneDescription): void
    {
        $this->phoneDescription = $phoneDescription;
    }

    public function getIsRepresentative(): int
    {
        return $this->isRepresentative;
    }

    public function setIsRepresentative(int $isRepresentative): void
    {
        $this->isRepresentative = $isRepresentative;
    }

    public function getLocationObj(): ?string
    {
        return $this->locationObj;
    }

    public function setLocationObj(?string $locationObj): void
    {
        $this->locationObj = $locationObj;
    }

    public function getPhones(): ?string
    {
        return $this->phones;
    }

    public function setPhones(?string $phones): void
    {
        $this->phones = $phones;
    }

    public function getFeatures(): ?string
    {
        return $this->features;
    }

    public function setFeatures(?string $features): void
    {
        $this->features = $features;
    }

    public function getContactPhone(): ?string
    {
        return $this->contactPhone;
    }

    public function setContactPhone(?string $contactPhone): void
    {
        $this->contactPhone = $contactPhone;
    }

    public function getContactEmail(): ?string
    {
        return $this->contactEmail;
    }

    public function setContactEmail(?string $contactEmail): void
    {
        $this->contactEmail = $contactEmail;
    }

    public function getContactPosition(): ?string
    {
        return $this->contactPosition;
    }

    public function setContactPosition(?string $contactPosition): void
    {
        $this->contactPosition = $contactPosition;
    }

    public function toArray(): array
    {
        return [
            'id'                => $this->getId(),
            'location'          => $this->getLocation(),
            'geolocation'       => $this->getGeolocation(),
            'working_hours'     => $this->getWorkingHours(),
            'email'             => $this->getEmail(),
            'phone'             => $this->getPhone(),
            'phone_description' => $this->getPhoneDescription(),
        ];
    }
}
