<?php

declare(strict_types=1);

namespace App\Modules\Audio\Entity\AudioPlaylist;

use Doctrine\ORM\Mapping as ORM;
use DomainException;

#[ORM\Entity]
#[ORM\Table(name: 'audio_playlist')]
#[ORM\Index(fields: ['name'], name: 'IDX_SEARCH')]
class AudioPlaylist
{
    private const LIMIT_TOTAL       = 10000;
    private const LIMIT_DAILY       = 1000;
    private const TIME_TO_RESTORE   = 24 * 60 * 60;

    #[ORM\Id]
    #[ORM\Column(type: 'integer', unique: true)]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private ?int $id = null;

    #[ORM\Column(type: 'integer')]
    private int $unionId;

    #[ORM\Column(type: 'audio_playlist_photo', nullable: true)]
    private ?Fields\Photo $photo;

    #[ORM\Column(type: 'audio_playlist_photo_host', nullable: true)]
    private ?Fields\PhotoHost $photoHost;

    #[ORM\Column(type: 'audio_playlist_photo_file_id', nullable: true)]
    private ?Fields\PhotoFileId $photoFileId;

    #[ORM\Column(type: 'string', length: 400)]
    private string $name;

    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    private bool $explicit = false;

    #[ORM\Column(type: 'string', length: 500)]
    private string $artists;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $duration = 0;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $countAudio = 0;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $countAdd = 0;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $publishedAt = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $createdAt = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $updatedAt = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $deletedAt = null;

    private function __construct(
        int $unionId,
        string $name,
        string $artists,
        ?Fields\Photo $photo,
        ?Fields\PhotoHost $photoHost,
        ?Fields\PhotoFileId $photoFileId
    ) {
        $this->unionId = $unionId;
        $this->name = $name;
        $this->artists = $artists;
        $this->photo = $photo;
        $this->photoHost = $photoHost;
        $this->photoFileId = $photoFileId;
    }

    public static function create(
        int $unionId,
        string $name,
        string $artists,
        ?Fields\Photo $photo = null,
        ?Fields\PhotoHost $photoHost = null,
        ?Fields\PhotoFileId $photoFileId = null
    ): self {
        $item = new self(
            unionId: $unionId,
            name: $name,
            artists: $artists,
            photo: $photo,
            photoHost: $photoHost,
            photoFileId: $photoFileId
        );

        $item->description = null;
        $item->publishedAt = null;
        $item->createdAt = time();
        $item->updatedAt = null;
        $item->deletedAt = null;

        return $item;
    }

    public function edit(
        string $name,
        ?string $description
    ): void {
        // todo: !!!!

        $this->name = $name;
        $this->description = $description;

        $this->updatedAt = time();
    }

    public function markDeleted(): void
    {
        $this->deletedAt = time();
    }

    public function canRestore(): bool
    {
        if (null === $this->deletedAt) {
            return false;
        }

        return time() - $this->deletedAt < self::TIME_TO_RESTORE;
    }

    public function restore(): void
    {
        $this->deletedAt = null;
    }

    public static function limitTotal(): int
    {
        return self::LIMIT_TOTAL;
    }

    public static function limitDaily(): int
    {
        return self::LIMIT_DAILY;
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

    public function getPhoto(): ?Fields\Photo
    {
        return $this->photo;
    }

    public function setPhoto(?Fields\Photo $photo): void
    {
        $this->photo = $photo;
    }

    public function getPhotoHost(): ?Fields\PhotoHost
    {
        return $this->photoHost;
    }

    public function setPhotoHost(?Fields\PhotoHost $photoHost): void
    {
        $this->photoHost = $photoHost;
    }

    public function getPhotoFileId(): ?Fields\PhotoFileId
    {
        return $this->photoFileId;
    }

    public function setPhotoFileId(?Fields\PhotoFileId $photoFileId): void
    {
        $this->photoFileId = $photoFileId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function isExplicit(): bool
    {
        return $this->explicit;
    }

    public function setExplicit(bool $explicit): void
    {
        $this->explicit = $explicit;
    }

    public function getArtist(): string
    {
        return $this->artists;
    }

    public function setArtists(string $artists): void
    {
        $this->artists = $artists;
    }

    public function getArtists(): array
    {
        return array_map('trim', explode(',', $this->artists));
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getDuration(): int
    {
        return $this->duration;
    }

    public function setDuration(int $duration): void
    {
        $this->duration = $duration;
    }

    public function getCountAudio(): int
    {
        return $this->countAudio;
    }

    public function setCountAudio(int $countAudio): void
    {
        $this->countAudio = $countAudio;
    }

    public function getCountAdd(): int
    {
        return $this->countAdd;
    }

    public function setCountAdd(int $countAdd): void
    {
        $this->countAdd = $countAdd;
    }

    public function getPublishedAt(): ?int
    {
        return $this->publishedAt;
    }

    public function publish(int $publishedAt): void
    {
        $this->publishedAt = $publishedAt;
    }

    public function unPublish(): void
    {
        $this->publishedAt = null;
    }

    public function getCreatedAt(): ?int
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?int $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getUpdatedAt(): ?int
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?int $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    public function getDeletedAt(): ?int
    {
        return $this->deletedAt;
    }

    public function setDeletedAt(?int $deletedAt): void
    {
        $this->deletedAt = $deletedAt;
    }

    public function toArray(): array
    {
        return [
            'id'            => $this->getId(),
            'union_id'      => $this->getUnionId(),
            'photo'         => $this->getPhoto()?->getValue(),
            'name'          => $this->getName(),
            'description'   => $this->getDescription(),
            'explicit'      => $this->isExplicit(),
            'duration'      => $this->getDuration(),
            'count_audio'   => $this->getCountAudio(),
            'count_add'     => $this->getCountAdd(),
            'updated_at'    => $this->getUpdatedAt(),
            'published_at'  => $this->getPublishedAt(),
        ];
    }
}
