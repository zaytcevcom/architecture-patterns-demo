<?php

declare(strict_types=1);

namespace App\Modules\Audio\Entity\Audio;

use App\Modules\Audio\Entity\AudioAlbum\AudioAlbum;
use App\Modules\Audio\Entity\AudioGenre\AudioGenre;
use App\Modules\Identity\Entity\User\User;
use Doctrine\ORM\Mapping as ORM;
use DomainException;

#[ORM\Entity]
#[ORM\Table(name: 'audios')]
#[ORM\Index(fields: ['name'], name: 'IDX_SEARCH')]
class Audio
{
    private const LIMIT_TOTAL       = 10000;
    private const LIMIT_DAILY       = 1000;
    private const TIME_TO_RESTORE   = 24 * 60 * 60;

    #[ORM\Id]
    #[ORM\Column(type: 'integer', unique: true)]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private ?int $id = null;

    #[ORM\Column(type: 'integer', nullable: true, options: ['default' => 0])]
    private ?int $ind = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $nameBlock = null;

    #[ORM\ManyToOne(targetEntity: AudioAlbum::class)]
    #[ORM\JoinColumn(name: 'album_id', referencedColumnName: 'id', unique: false, nullable: true)]
    private ?AudioAlbum $album;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', unique: false, nullable: true)]
    private ?User $user;

    #[ORM\Column(type: 'audio_source', nullable: true)]
    private ?Fields\Source $source;

    #[ORM\Column(type: 'audio_source_host', nullable: true)]
    private ?Fields\SourceHost $sourceHost;

    #[ORM\Column(type: 'audio_source_file_id', nullable: true)]
    private ?Fields\SourceFileId $sourceFileId;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $sourceDeletedAt;

    #[ORM\Column(type: 'string', length: 255)]
    private string $fileId = '';

    #[ORM\Column(type: 'string', length: 300)]
    private string $url;

    #[ORM\Column(type: 'string', length: 300, nullable: true)]
    private ?string $urlHls = null;

    #[ORM\Column(type: 'string', length: 300, nullable: true)]
    private ?string $coverUrl = null;

    #[ORM\Column(type: 'string', length: 400)]
    private string $name;

    #[ORM\Column(type: 'string', length: 150, nullable: true)]
    private ?string $version = null;

    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    private bool $explicit = false;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $lyrics = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $lyricsHost = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $lyricsFileId = null;

    #[ORM\Column(type: 'string', length: 250)]
    private string $artist;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $duration = 0;

    #[ORM\ManyToOne(targetEntity: AudioGenre::class)]
    #[ORM\JoinColumn(name: 'genre_id', referencedColumnName: 'id', unique: false, nullable: true)]
    private ?AudioGenre $genre = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $text = null;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $countAdd = 0;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $date = 0;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $top = 0;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $hide = 0;

    #[ORM\Column(name: '_temp', type: 'integer')]
    private int $temp = 0;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $createdAt;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $updatedAt;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $deletedAt;

    private function __construct(
        string $name,
        string $artist,
        Fields\Source $source,
        Fields\SourceHost $sourceHost,
        Fields\SourceFileId $sourceFileId,
        string $url,
        User $user,
        ?AudioAlbum $album
    ) {
        $this->name = $name;
        $this->artist = $artist;
        $this->source = $source;
        $this->sourceHost = $sourceHost;
        $this->sourceFileId = $sourceFileId;
        $this->sourceDeletedAt = null;
        $this->url = $url;
        $this->user = $user;
        $this->album = $album;

        $this->createdAt = time();
        $this->updatedAt = null;
        $this->deletedAt = null;

        $this->date = time();
        $this->hide = 0;
    }

    public static function create(
        string $name,
        string $artist,
        Fields\Source $source,
        Fields\SourceHost $sourceHost,
        Fields\SourceFileId $sourceFileId,
        string $url,
        User $user,
        ?AudioAlbum $album,
    ): self {
        // todo: !!!!

        return new self(
            name: $name,
            artist: $artist,
            source: $source,
            sourceHost: $sourceHost,
            sourceFileId: $sourceFileId,
            url: $url,
            user: $user,
            album: $album,
        );
    }

    public function edit(
        string $name
    ): void {
        // todo: !!!!

        $this->name = $name;

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

    public function getInd(): ?int
    {
        return $this->ind;
    }

    public function setInd(?int $ind): void
    {
        $this->ind = $ind;
    }

    public function getNameBlock(): ?string
    {
        return $this->nameBlock;
    }

    public function setNameBlock(?string $nameBlock): void
    {
        $this->nameBlock = $nameBlock;
    }

    public function getAlbum(): ?AudioAlbum
    {
        return $this->album;
    }

    public function setAlbum(?AudioAlbum $album): void
    {
        $this->album = $album;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): void
    {
        $this->user = $user;
    }

    public function getSource(): ?Fields\Source
    {
        return $this->source;
    }

    public function setSource(?Fields\Source $source): void
    {
        $this->source = $source;
    }

    public function getSourceHost(): ?Fields\SourceHost
    {
        return $this->sourceHost;
    }

    public function setSourceHost(?Fields\SourceHost $sourceHost): void
    {
        $this->sourceHost = $sourceHost;
    }

    public function getSourceFileId(): ?Fields\SourceFileId
    {
        return $this->sourceFileId;
    }

    public function setSourceFileId(?Fields\SourceFileId $sourceFileId): void
    {
        $this->sourceFileId = $sourceFileId;
    }

    public function getSourceDeletedAt(): ?int
    {
        return $this->sourceDeletedAt;
    }

    public function setSourceDeletedAt(?int $sourceDeletedAt): void
    {
        $this->sourceDeletedAt = $sourceDeletedAt;
    }

    public function getFileId(): string
    {
        return $this->fileId;
    }

    public function setFileId(string $fileId): void
    {
        $this->fileId = $fileId;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setUrl(string $url): void
    {
        $this->url = $url;
    }

    public function getUrlHls(): ?string
    {
        return $this->urlHls;
    }

    public function setUrlHls(?string $urlHls): void
    {
        $this->urlHls = $urlHls;
    }

    public function getCoverUrl(): ?string
    {
        return $this->coverUrl;
    }

    public function setCoverUrl(?string $coverUrl): void
    {
        $this->coverUrl = $coverUrl;
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

    public function hasLyrics(): bool
    {
        return null !== $this->lyricsFileId;
    }

    public function getLyrics(): ?string
    {
        return $this->lyrics;
    }

    public function setLyrics(?string $lyrics): void
    {
        $this->lyrics = $lyrics;
    }

    public function getLyricsHost(): ?string
    {
        return $this->lyricsHost;
    }

    public function setLyricsHost(?string $lyricsHost): void
    {
        $this->lyricsHost = $lyricsHost;
    }

    public function getLyricsFileId(): ?string
    {
        return $this->lyricsFileId;
    }

    public function setLyricsFileId(?string $lyricsFileId): void
    {
        $this->lyricsFileId = $lyricsFileId;
    }

    public function getArtist(): string
    {
        return $this->artist;
    }

    public function setArtist(string $artist): void
    {
        $this->artist = $artist;
    }

    public function getArtists(): array
    {
        return array_map('trim', explode(',', $this->artist));
    }

    public function getDuration(): int
    {
        return $this->duration;
    }

    public function setDuration(int $duration): void
    {
        $this->duration = $duration;
    }

    public function getGenre(): ?AudioGenre
    {
        return $this->genre;
    }

    public function setGenre(?AudioGenre $genre): void
    {
        $this->genre = $genre;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(?string $text): void
    {
        $this->text = $text;
    }

    public function getCountAdd(): int
    {
        return $this->countAdd;
    }

    public function setCountAdd(int $countAdd): void
    {
        $this->countAdd = $countAdd;
    }

    public function getDate(): int
    {
        return $this->date;
    }

    public function setDate(int $date): void
    {
        $this->date = $date;
    }

    public function getTop(): int
    {
        return $this->top;
    }

    public function setTop(int $top): void
    {
        $this->top = $top;
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
            'album_id'      => $this->getAlbum()?->getId(),
            'name'          => $this->getName(),
            'version'       => $this->getVersion(),
            'explicit'      => $this->isExplicit(),
            'artists'       => $this->getArtists(),
            'duration'      => $this->getDuration(),
            'has_lyrics'    => $this->hasLyrics(),
            'lyrics_file_id' => $this->getLyricsFileId(),
            'url'           => $this->getUrl(),
            'url_hls'       => $this->getUrlHls(),
            'count_add'     => $this->getCountAdd(),
        ];
    }
}
