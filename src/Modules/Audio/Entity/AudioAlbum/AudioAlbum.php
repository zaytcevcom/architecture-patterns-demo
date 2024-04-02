<?php

declare(strict_types=1);

namespace App\Modules\Audio\Entity\AudioAlbum;

use Doctrine\ORM\Mapping as ORM;
use DomainException;

#[ORM\Entity]
#[ORM\Table(name: 'audios_albums')]
#[ORM\Index(fields: ['id', 'deletedAt'], name: 'IDX_GLOBAL_SEARCH')]
#[ORM\Index(fields: ['id', 'unionId', 'deletedAt', 'audioCount', 'year'], name: 'IDX_UNION')]
class AudioAlbum
{
    private const LIMIT_TOTAL       = 10000;
    private const LIMIT_DAILY       = 1000;
    private const TIME_TO_RESTORE   = 24 * 60 * 60;

    #[ORM\Id]
    #[ORM\Column(type: 'integer', unique: true)]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private ?int $id = null;

    #[ORM\Column(type: 'integer', nullable: true, options: ['default' => 0])]
    private int $ind = 0;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $unionLabelId = null;

    #[ORM\Column(type: 'string', length: 300)]
    private string $unionIds = '';

    #[ORM\Column(type: 'audio_album_photo', nullable: true)]
    private ?Fields\Photo $photo;

    #[ORM\Column(type: 'audio_album_photo_host', nullable: true)]
    private ?Fields\PhotoHost $photoHost;

    #[ORM\Column(type: 'audio_album_photo_file_id', nullable: true)]
    private ?Fields\PhotoFileId $photoFileId;

    #[ORM\Column(type: 'audio_album_photo_animated', nullable: true)]
    private ?Fields\PhotoAnimated $photoAnimated = null;

    #[ORM\Column(type: 'audio_album_photo_animated_host', nullable: true)]
    private ?Fields\PhotoAnimatedHost $photoAnimatedHost = null;

    #[ORM\Column(type: 'audio_album_photo_animated_file_id', nullable: true)]
    private ?Fields\PhotoAnimatedFileId $photoAnimatedFileId = null;

    #[ORM\Column(type: 'string', length: 255)]
    private string $fileId = '';

    #[ORM\Column(type: 'text')]
    private string $coverUrl = '';

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $covers = null;

    #[ORM\Column(type: 'string', length: 400)]
    private string $name;

    #[ORM\Column(type: 'string', length: 200, nullable: true)]
    private ?string $version = null;

    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    private bool $explicit = false;

    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    private bool $isAlbum = false;

    #[ORM\Column(type: 'string', length: 500)]
    private string $artists;

    #[ORM\Column(type: 'integer')]
    private int $year;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $genreIds = null;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $audioCount = 0;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $audioIds = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $duration = 0;

    #[ORM\Column(type: 'integer')]
    private int $date;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $countAdd = 0;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $countLikes = 0;

    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    private bool $visible = true;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $hide = 0;

    #[ORM\Column(name: '_temp', type: 'integer')]
    private int $temp = 0;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $createdAt = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $updatedAt = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $deletedAt = null;

    private function __construct(
        string $name,
        string $artists,
        int $year,
        Fields\Photo $photo,
        Fields\PhotoHost $photoHost,
        Fields\PhotoFileId $photoFileId
    ) {
        $this->name = $name;
        $this->artists = $artists;
        $this->year = $year;
        $this->photo = $photo;
        $this->photoHost = $photoHost;
        $this->photoFileId = $photoFileId;

        $this->createdAt = time();
        $this->updatedAt = null;
        $this->deletedAt = null;

        $this->date = time();
        $this->hide = 0;
    }

    public static function create(
        string $name,
        string $artists,
        int $year,
        Fields\Photo $photo,
        Fields\PhotoHost $photoHost,
        Fields\PhotoFileId $photoFileId
    ): self {
        $item = new self(
            name: $name,
            artists: $artists,
            year: $year,
            photo: $photo,
            photoHost: $photoHost,
            photoFileId: $photoFileId
        );

        // todo: !!!!

        $item->photo           = $photo;
        $item->photoHost       = $photoHost;
        $item->photoFileId     = $photoFileId;
        $item->description     = null;

        $fileId = $photoFileId->getValue();

        if (null === $fileId) {
            throw new DomainException('File id not exist');
        }
        $item->fileId = $fileId;

        return $item;
    }

    public function edit(
        ?string $description
    ): void {
        // todo: !!!!

        $this->description = $description;

        $this->updatedAt = time();
    }

    public function markDeleted(): void
    {
        $this->hide = time();
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
        $this->hide = 0;
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

    public function getInd(): int
    {
        return $this->ind;
    }

    public function setInd(int $ind): void
    {
        $this->ind = $ind;
    }

    public function getUnionLabelId(): ?int
    {
        return $this->unionLabelId;
    }

    public function setUnionLabelId(?int $unionLabelId): void
    {
        $this->unionLabelId = $unionLabelId;
    }

    public function getUnionIds(): string
    {
        return $this->unionIds;
    }

    public function setUnionIds(string $unionIds): void
    {
        $this->unionIds = $unionIds;
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

    public function getPhotoAnimated(): ?Fields\PhotoAnimated
    {
        return $this->photoAnimated;
    }

    public function setPhotoAnimated(?Fields\PhotoAnimated $photoAnimated): void
    {
        $this->photoAnimated = $photoAnimated;
    }

    public function getPhotoAnimatedHost(): ?Fields\PhotoAnimatedHost
    {
        return $this->photoAnimatedHost;
    }

    public function setPhotoAnimatedHost(?Fields\PhotoAnimatedHost $photoAnimatedHost): void
    {
        $this->photoAnimatedHost = $photoAnimatedHost;
    }

    public function getPhotoAnimatedFileId(): ?Fields\PhotoAnimatedFileId
    {
        return $this->photoAnimatedFileId;
    }

    public function setPhotoAnimatedFileId(?Fields\PhotoAnimatedFileId $photoAnimatedFileId): void
    {
        $this->photoAnimatedFileId = $photoAnimatedFileId;
    }

    public function getFileId(): string
    {
        return $this->fileId;
    }

    public function setFileId(string $fileId): void
    {
        $this->fileId = $fileId;
    }

    public function getCoverUrl(): string
    {
        return $this->coverUrl;
    }

    public function setCoverUrl(string $coverUrl): void
    {
        $this->coverUrl = $coverUrl;
    }

    public function getCovers(): array
    {
        return (!empty($this->covers)) ? (array)json_decode($this->covers, true) : [];
    }

    public function setCovers(?string $covers): void
    {
        $this->covers = $covers;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getVersion(): ?string
    {
        return $this->version;
    }

    public function setVersion(?string $version): void
    {
        $this->version = $version;
    }

    public function isExplicit(): bool
    {
        return $this->explicit;
    }

    public function setExplicit(bool $explicit): void
    {
        $this->explicit = $explicit;
    }

    public function isAlbum(): bool
    {
        return $this->isAlbum;
    }

    public function setIsAlbum(bool $isAlbum): void
    {
        $this->isAlbum = $isAlbum;
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

    public function getYear(): int
    {
        return $this->year;
    }

    public function setYear(int $year): void
    {
        $this->year = $year;
    }

    public function getGenreIds(): ?string
    {
        return $this->genreIds;
    }

    public function setGenreIds(?string $genreIds): void
    {
        $this->genreIds = $genreIds;
    }

    public function getAudioCount(): int
    {
        return $this->audioCount;
    }

    public function setAudioCount(int $audioCount): void
    {
        $this->audioCount = $audioCount;
    }

    public function getAudioIds(): ?string
    {
        return $this->audioIds;
    }

    public function setAudioIds(?string $audioIds): void
    {
        $this->audioIds = $audioIds;
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

    public function getDate(): int
    {
        return $this->date;
    }

    public function setDate(int $date): void
    {
        $this->date = $date;
    }

    public function getCountAdd(): int
    {
        return $this->countAdd;
    }

    public function setCountAdd(int $countAdd): void
    {
        $this->countAdd = $countAdd;
    }

    public function getCountLikes(): int
    {
        return $this->countLikes;
    }

    public function setCountLikes(int $countLikes): void
    {
        $this->countLikes = $countLikes;
    }

    public function isVisible(): bool
    {
        return $this->visible;
    }

    public function setVisible(bool $visible): void
    {
        $this->visible = $visible;
    }

    public function getHide(): int
    {
        return $this->hide;
    }

    public function setHide(int $hide): void
    {
        $this->hide = $hide;
    }

    public function getTemp(): int
    {
        return $this->temp;
    }

    public function setTemp(int $temp): void
    {
        $this->temp = $temp;
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
            'genre_ids'     => $this->getGenreIds(),
            'name'          => $this->getName(),
            'cover_url'     => $this->getCoverUrl(),
            'description'   => $this->getDescription(),
            'version'       => $this->getVersion(),
            'explicit'      => $this->isExplicit(),
            'duration'      => $this->getDuration(),
            'year'          => $this->getYear(),
            'audio_count'   => $this->getAudioCount(),
            'count_add'     => $this->getCountAdd(),
            'count_likes'   => $this->getCountLikes(),
        ];
    }
}
