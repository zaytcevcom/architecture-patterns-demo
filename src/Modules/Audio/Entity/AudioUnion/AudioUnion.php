<?php

declare(strict_types=1);

namespace App\Modules\Audio\Entity\AudioUnion;

use Doctrine\ORM\Mapping as ORM;
use DomainException;

#[ORM\Entity]
#[ORM\Table(name: 'audio_union')]
#[ORM\Index(fields: ['audioId'], name: 'IDX_AUDIO')]
#[ORM\Index(fields: ['unionId'], name: 'IDX_UNION')]
class AudioUnion
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer', unique: true)]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private ?int $id = null;

    #[ORM\Column(type: 'integer')]
    private int $audioId;

    #[ORM\Column(type: 'integer')]
    private int $unionId;

    private function __construct(
        int $audioId,
        int $unionId,
    ) {
        $this->audioId = $audioId;
        $this->unionId = $unionId;
    }

    public static function create(
        int $audioId,
        int $unionId,
    ): self {
        return new self(
            audioId: $audioId,
            unionId: $unionId,
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

    public function getUnionId(): int
    {
        return $this->unionId;
    }

    public function setUnionId(int $unionId): void
    {
        $this->unionId = $unionId;
    }
}
