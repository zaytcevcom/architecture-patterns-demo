<?php

declare(strict_types=1);

namespace App\Modules\Audio\Entity\AudioListen;

use Doctrine\ORM\Mapping as ORM;
use DomainException;

#[ORM\Entity]
#[ORM\Table(name: 'audio_listen')]
#[ORM\Index(fields: ['audioId'], name: 'IDX_AUDIO')]
#[ORM\Index(fields: ['userId'], name: 'IDX_USER')]
class AudioListen
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer', unique: true)]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private ?int $id = null;

    #[ORM\Column(type: 'integer')]
    private int $audioId;

    #[ORM\Column(type: 'integer')]
    private int $userId;

    #[ORM\Column(type: 'integer')]
    private int $time;

    private function __construct(
        int $audioId,
        int $userId,
        int $time
    ) {
        $this->audioId = $audioId;
        $this->userId = $userId;
        $this->time = $time;
    }

    public static function create(
        int $audioId,
        int $userId,
        int $time,
    ): self {
        return new self(
            audioId: $audioId,
            userId: $userId,
            time: $time,
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

    public function getAudioId(): int
    {
        return $this->audioId;
    }

    public function setAudioId(int $audioId): void
    {
        $this->audioId = $audioId;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function setUserId(int $userId): void
    {
        $this->userId = $userId;
    }

    public function getTime(): int
    {
        return $this->time;
    }

    public function setTime(int $time): void
    {
        $this->time = $time;
    }
}
