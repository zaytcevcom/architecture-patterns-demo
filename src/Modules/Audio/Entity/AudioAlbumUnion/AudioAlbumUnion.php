<?php

declare(strict_types=1);

namespace App\Modules\Audio\Entity\AudioAlbumUnion;

use Doctrine\ORM\Mapping as ORM;
use DomainException;

#[ORM\Entity]
#[ORM\Table(name: 'audio_album_union')]
#[ORM\Index(fields: ['albumId'], name: 'IDX_ALBUM')]
#[ORM\Index(fields: ['unionId'], name: 'IDX_UNION')]
class AudioAlbumUnion
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer', unique: true)]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private ?int $id = null;

    #[ORM\Column(type: 'integer')]
    private int $albumId;

    #[ORM\Column(type: 'integer')]
    private int $unionId;

    private function __construct(
        int $albumId,
        int $unionId,
    ) {
        $this->albumId = $albumId;
        $this->unionId = $unionId;
    }

    public static function create(
        int $albumId,
        int $unionId,
    ): self {
        return new self(
            albumId: $albumId,
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

    public function getAlbumId(): int
    {
        return $this->albumId;
    }

    public function setAlbumId(int $albumId): void
    {
        $this->albumId = $albumId;
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
