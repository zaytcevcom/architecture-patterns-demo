<?php

declare(strict_types=1);

namespace App\Modules\Union\Entity\UnionEventInfo;

use Doctrine\ORM\Mapping as ORM;
use DomainException;

#[ORM\Entity]
#[ORM\Table(name: 'unions_events')]
#[ORM\Index(fields: ['unionId'], name: 'union_id')]
#[ORM\Index(fields: ['ownerId'], name: 'union_id')]
class UnionEventInfo
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer', unique: true)]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private ?int $id = null;

    #[ORM\Column(type: 'integer')]
    private int $unionId;

    #[ORM\Column(type: 'integer')]
    private int $ownerId;

    #[ORM\Column(type: 'string', length: 500, nullable: true)]
    private ?string $location;

    #[ORM\Column(type: 'string', length: 200, nullable: true)]
    private ?string $geolocation;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $timeStart;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $timeEnd;

    private function __construct(
        int $unionId,
        int $ownerId,
        ?int $timeStart,
        ?int $timeEnd,
    ) {
        $this->unionId = $unionId;
        $this->ownerId = $ownerId;
        $this->timeStart = $timeStart;
        $this->timeEnd = $timeEnd;
        $this->location = null;
        $this->geolocation = null;
    }

    public static function create(
        int $unionId,
        int $placeId,
        ?int $timeStart,
        ?int $timeEnd
    ): self {
        return new self(
            unionId: $unionId,
            ownerId: -1 * $placeId,
            timeStart: $timeStart,
            timeEnd: $timeEnd,
        );
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

    public function getOwnerId(): int
    {
        return $this->ownerId;
    }

    public function setOwnerId(int $ownerId): void
    {
        $this->ownerId = $ownerId;
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

    public function setGeolocation(?string $geolocation): void
    {
        $this->geolocation = $geolocation;
    }

    public function getTimeStart(): ?int
    {
        return $this->timeStart;
    }

    public function setTimeStart(?int $timeStart): void
    {
        $this->timeStart = $timeStart;
    }

    public function getTimeEnd(): ?int
    {
        return $this->timeEnd;
    }

    public function setTimeEnd(?int $timeEnd): void
    {
        $this->timeEnd = $timeEnd;
    }

    public function toArray(): array
    {
        return [
            'id'          => $this->getId(),
            'location'    => $this->getLocation(),
            'geolocation' => $this->getGeolocation(),
            'time_start'  => $this->getTimeStart(),
            'time_end'    => $this->getTimeEnd(),
        ];
    }
}
