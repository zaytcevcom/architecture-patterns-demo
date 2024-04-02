<?php

declare(strict_types=1);

namespace App\Modules\Union\Entity\Union;

use Doctrine\ORM\Mapping as ORM;
use DomainException;

#[ORM\Entity]
#[ORM\Table(name: 'unions')]
#[ORM\Index(fields: ['name'], name: 'IDX_SEARCH')]
class Union
{
    private const LIMIT_TOTAL       = 10000;
    private const LIMIT_DAILY       = 1000;
    private const TIME_TO_RESTORE   = 24 * 60 * 60;

    // Тип объединения (сообщество, место, событие)
    private const TYPE_COMMUNITY    = 0;
    private const TYPE_PLACE        = 1;
    private const TYPE_EVENT        = 2;

    // Вид сообщества (открытое, закрытое, частное)
    private const KIND_PUBLIC     = 0;
    private const KIND_CLOSED     = 1;
    private const KIND_PRIVATE    = 2;

    // Роли
    private const ROLE_REQUEST      = -2; 	// Пользователь отправил заявку на вступление
    private const ROLE_INVITE       = -1; 	// Пользователю выслали приглашение на вступление
    private const ROLE_MEMBER       = 0; 	// Обычный пользователь
    private const ROLE_ADMIN        = 1;	// Администратор
    private const ROLE_EDITOR       = 2;	// Редактор
    private const ROLE_MODER        = 3;	// Модератор
    private const ROLE_CREATOR      = 7;	// Создатель

    #[ORM\Id]
    #[ORM\Column(type: 'integer', unique: true)]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private ?int $id = null;

    #[ORM\Column(type: 'integer', options: ['unsigned' => true])]
    private int $creatorId;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $type = 0;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $access = 0;

    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    private ?string $screenName = null;

    #[ORM\Column(type: 'string', length: 255)]
    private string $name;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $status = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $photoId = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $albumProfileId = null;

    #[ORM\Column(type: 'union_photo', nullable: true)]
    private ?Fields\Photo $photo = null;

    #[ORM\Column(type: 'union_photo_host', nullable: true)]
    private ?Fields\PhotoHost $photoHost = null;

    #[ORM\Column(type: 'union_photo_file_id', nullable: true)]
    private ?Fields\PhotoFileId $photoFileId = null;

    #[ORM\Column(type: 'union_cover', nullable: true)]
    private ?Fields\Cover $cover = null;

    #[ORM\Column(type: 'union_cover_host', nullable: true)]
    private ?Fields\CoverHost $coverHost = null;

    #[ORM\Column(type: 'union_cover_file_id', nullable: true)]
    private ?Fields\CoverFileId $coverFileId = null;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $ageLimits = 0;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $ageLimitsTime = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $website = null;

    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    private bool $verified = false;

    #[ORM\Column(type: 'string', length: 500, nullable: true)]
    private ?string $sections = null;

    #[ORM\Column(type: 'integer', nullable: true, options: ['unsigned' => true])]
    private ?int $countryId = null;

    #[ORM\Column(type: 'integer', nullable: true, options: ['unsigned' => true])]
    private ?int $cityId = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $categoryId = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $subcategoryId = null;

    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    private bool $isMusic = false;

    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    private bool $isMedia = false;

    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    private bool $isArtist = false;

    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    private bool $recommendation = false;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $countMembers = 0;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $countAudios = 0;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $countAudioLikes = 0;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $countPhotos = 0;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $countAlbums = 0;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $countAudioAlbums = 0;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $countAudioSingles = 0;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $countPosts = 0;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $countFlows = 0;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $countFlowViews = 0;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $countVideos = 0;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $countLinks = 0;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $countContacts = 0;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $countEvents = 0;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $countRadios = 0;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $countStocks = 0;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $countStickers = 0;

    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    private bool $membersHide = false;

    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    private bool $approved = false;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $messagesCount = 0;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $membersCount = 0;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $postsCount = 0;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $audiosCount = 0;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $albumsCount = 0;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $photosCount = 0;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $videosCount = 0;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $linksCount = 0;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $contactsCount = 0;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $eventsCount = 0;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $radiosCount = 0;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $stocksCount = 0;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $stickersCount = 0;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $giftsCount = 0;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $dateCreate = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $timeCreate = null;

    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    private ?string $photoFile = null;

    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    private ?string $coverFile = null;

    #[ORM\Column(type: 'string', length: 500, nullable: true)]
    private ?string $mainGenreIds = null;

    #[ORM\Column(type: 'string', length: 500, nullable: true)]
    private ?string $addGenreIds = null;

    #[ORM\Column(type: 'decimal', precision: 11, scale: 2, options: ['default' => 0])]
    private float $balance = 0;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $oldPhoto = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $oldCover = null;

    private function __construct(
        int $type,
        string $name,
        int $creatorId,
        ?int $categoryId = null,
        ?string $description = null,
        ?string $website = null
    ) {
        $this->type = $type;
        $this->name = $name;
        $this->creatorId = $creatorId;
        $this->description = $description;
        $this->categoryId = $categoryId;
        $this->website = $website;

        // todo: legacy
        $this->setSections('{"posts":1,"albums":0,"videos":1,"audios":1,"contacts":1,"links":1,"comments":1,"messages":1,"stickers":1,"radios":1,"lives":1}');
    }

    public static function createCommunity(
        string $name,
        int $creatorId,
        ?int $categoryId = null,
        ?string $description = null,
        ?string $website = null
    ): self {
        return new self(
            type: self::typeCommunity(),
            name: $name,
            creatorId: $creatorId,
            categoryId: $categoryId,
            description: $description,
            website: $website
        );
    }

    public static function createCommunityMusical(
        string $name,
        int $creatorId,
        ?int $categoryId = null,
        ?string $description = null,
        ?string $website = null
    ): self {
        $union = new self(
            type: self::typeCommunity(),
            name: $name,
            creatorId: $creatorId,
            categoryId: $categoryId,
            description: $description,
            website: $website
        );

        $union->setIsMusical();

        return $union;
    }

    public static function createPlace(
        string $name,
        int $creatorId,
        ?int $categoryId = null,
        ?string $description = null,
        ?string $website = null,
    ): self {
        return new self(
            type: self::typePlace(),
            name: $name,
            creatorId: $creatorId,
            categoryId: $categoryId,
            description: $description,
            website: $website
        );
    }

    public static function createEvent(
        string $name,
        int $creatorId,
        ?int $categoryId = null,
        ?string $description = null,
    ): self {
        return new self(
            type: self::typeEvent(),
            name: $name,
            creatorId: $creatorId,
            categoryId: $categoryId,
            description: $description,
            website: null
        );
    }

    public function edit(
        string $name,
        ?string $description,
        int $categoryId,
        ?string $website,
        ?string $status,
    ): void {
        $this->name = $name;
        $this->description = $description;
        $this->categoryId = $categoryId;
        $this->website = $website;
        $this->status = $status;
    }

    public function editPrivacy(
        int $kind,
        bool $membersHide,
    ): void {
        $this->access = $kind;
        $this->membersHide = $membersHide;
    }

    /** @return array<string, array|string|null>|null */
    public static function getPhotoParsed(?string $photo): ?array
    {
        if (empty($photo)) {
            return null;
        }

        /** @var bool|string[]|null $sizes */
        $sizes = json_decode($photo, true);

        if (!\is_array($sizes)) {
            return null;
        }

        /** @var bool|string[]|null $cropSizes */
        $cropSizes = $sizes['crop_square'] ?? [];

        if (!\is_array($cropSizes)) {
            return null;
        }

        ksort($cropSizes);
        $cropSizes = array_values($cropSizes);

        if (\count($cropSizes) > 0) {
            return [
                'xs'        => $cropSizes[0] ?? $cropSizes[1] ?? $cropSizes[2] ?? $cropSizes[3] ?? $sizes['original'] ?? null,
                'sm'        => $cropSizes[1] ?? $cropSizes[2] ?? $cropSizes[3] ?? $sizes['original'] ?? null,
                'md'        => $cropSizes[2] ?? $cropSizes[3] ?? $sizes['original'] ?? null,
                'lg'        => $cropSizes[3] ?? $cropSizes[2] ?? $sizes['original'] ?? null,
                'original'  => $sizes['original'] ?? null,
            ];
        }

        return null;
    }

    public function markDeleted(): void
    {
        // todo
    }

    public function canRestore(): bool
    {
        // todo
        return false; // time() - $this->deletedAt < self::TIME_TO_RESTORE;
    }

    public function restore(): void
    {
        // todo
    }

    public static function typeCommunity(): int
    {
        return self::TYPE_COMMUNITY;
    }

    public static function typePlace(): int
    {
        return self::TYPE_PLACE;
    }

    public static function typeEvent(): int
    {
        return self::TYPE_EVENT;
    }

    public static function kindPublic(): int
    {
        return self::KIND_PUBLIC;
    }

    public static function kindClosed(): int
    {
        return self::KIND_CLOSED;
    }

    public static function kindPrivate(): int
    {
        return self::KIND_PRIVATE;
    }

    public static function limitTotal(): int
    {
        return self::LIMIT_TOTAL;
    }

    public static function limitDaily(): int
    {
        return self::LIMIT_DAILY;
    }

    public static function roleCreator(): int
    {
        return self::ROLE_CREATOR;
    }

    public static function roleAdmin(): int
    {
        return self::ROLE_ADMIN;
    }

    public static function roleEditor(): int
    {
        return self::ROLE_EDITOR;
    }

    public static function roleModerator(): int
    {
        return self::ROLE_MODER;
    }

    public static function roleMember(): int
    {
        return self::ROLE_MEMBER;
    }

    public static function roleInvite(): int
    {
        return self::ROLE_INVITE;
    }

    public static function roleRequest(): int
    {
        return self::ROLE_REQUEST;
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

    public function getCreatorId(): int
    {
        return $this->creatorId;
    }

    public function setCreatorId(int $creatorId): void
    {
        $this->creatorId = $creatorId;
    }

    public function getType(): int
    {
        return $this->type;
    }

    public function setType(int $type): void
    {
        $this->type = $type;
    }

    public function getKind(): int
    {
        return $this->access;
    }

    public function setKind(int $kind): void
    {
        $this->access = $kind;
    }

    public function getScreenName(): ?string
    {
        return $this->screenName;
    }

    public function setScreenName(?string $screenName): void
    {
        $this->screenName = $screenName;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): void
    {
        $this->status = $status;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getPhotoId(): ?int
    {
        return $this->photoId;
    }

    public function setPhotoId(int $photoId): void
    {
        $this->photoId = $photoId;
    }

    public function removePhotoId(): void
    {
        $this->photoId = null;
    }

    public function getAlbumProfileId(): ?int
    {
        return $this->albumProfileId;
    }

    public function setAlbumProfileId(?int $albumProfileId): void
    {
        $this->albumProfileId = $albumProfileId;
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

    public function getCover(): ?Fields\Cover
    {
        return $this->cover;
    }

    public function setCover(?Fields\Cover $cover): void
    {
        $this->cover = $cover;
    }

    public function getCoverHost(): ?Fields\CoverHost
    {
        return $this->coverHost;
    }

    public function setCoverHost(?Fields\CoverHost $coverHost): void
    {
        $this->coverHost = $coverHost;
    }

    public function getCoverFileId(): ?Fields\CoverFileId
    {
        return $this->coverFileId;
    }

    public function setCoverFileId(?Fields\CoverFileId $coverFileId): void
    {
        $this->coverFileId = $coverFileId;
    }

    public function getAgeLimit(): int
    {
        return $this->ageLimits;
    }

    public function setAgeLimit(int $ageLimit): void
    {
        $this->ageLimits = $ageLimit;
    }

    public function getSections(): ?string
    {
        return $this->sections;
    }

    public function setSections(?string $sections): void
    {
        $this->sections = $sections;
    }

    public function getCountryId(): ?int
    {
        return $this->countryId;
    }

    public function setCountryId(?int $countryId): void
    {
        $this->countryId = $countryId;
    }

    public function getCityId(): ?int
    {
        return $this->cityId;
    }

    public function setCityId(?int $cityId): void
    {
        $this->cityId = $cityId;
    }

    public function getCategoryId(): ?int
    {
        return $this->categoryId;
    }

    public function setCategoryId(?int $categoryId): void
    {
        $this->categoryId = $categoryId;
    }

    public function getSubcategoryId(): ?int
    {
        return $this->subcategoryId;
    }

    public function setSubcategoryId(?int $subcategoryId): void
    {
        $this->subcategoryId = $subcategoryId;
    }

    public function getCountAudioLikes(): int
    {
        return $this->countAudioLikes;
    }

    public function setCountAudioLikes(int $countAudioLikes): void
    {
        $this->countAudioLikes = $countAudioLikes;
    }

    public function getOldPhoto(): ?string
    {
        return $this->oldPhoto;
    }

    public function setOldPhoto(?string $oldPhoto): void
    {
        $this->oldPhoto = $oldPhoto;
    }

    public function getOldCover(): ?string
    {
        return $this->oldCover;
    }

    public function setOldCover(?string $oldCover): void
    {
        $this->oldCover = $oldCover;
    }

    public function isMusic(): bool
    {
        return $this->isMusic;
    }

    public function setIsMusical(): void
    {
        $this->isMusic = true;
    }

    public function getCountMembers(): int
    {
        return $this->countMembers;
    }

    public function setCountMembers(int $countMembers): void
    {
        $this->countMembers = $countMembers;
    }

    public function getCountAudios(): int
    {
        return $this->countAudios;
    }

    public function setCountAudios(int $countAudios): void
    {
        $this->countAudios = $countAudios;
    }

    public function getCountPhotos(): int
    {
        return $this->countPhotos;
    }

    public function setCountPhotos(int $countPhotos): void
    {
        $this->countPhotos = $countPhotos;
    }

    public function getCountAlbums(): int
    {
        return $this->countAlbums;
    }

    public function setCountAlbums(int $countAlbums): void
    {
        $this->countAlbums = $countAlbums;
    }

    public function getCountAudioAlbums(): int
    {
        return $this->countAudioAlbums;
    }

    public function setCountAudioAlbums(int $countAudioAlbums): void
    {
        $this->countAudioAlbums = $countAudioAlbums;
    }

    public function getCountAudioSingles(): int
    {
        return $this->countAudioSingles;
    }

    public function setCountAudioSingles(int $countAudioSingles): void
    {
        $this->countAudioSingles = $countAudioSingles;
    }

    public function getCountPosts(): int
    {
        return $this->countPosts;
    }

    public function setCountPosts(int $countPosts): void
    {
        $this->countPosts = $countPosts;
    }

    public function getCountFlows(): int
    {
        return $this->countFlows;
    }

    public function setCountFlows(int $countFlows): void
    {
        $this->countFlows = $countFlows;
    }

    public function getCountFlowViews(): int
    {
        return $this->countFlowViews;
    }

    public function setCountFlowViews(int $countFlowViews): void
    {
        $this->countFlowViews = $countFlowViews;
    }

    public function getCountVideos(): int
    {
        return $this->countVideos;
    }

    public function setCountVideos(int $countVideos): void
    {
        $this->countVideos = $countVideos;
    }

    public function getCountLinks(): int
    {
        return $this->countLinks;
    }

    public function setCountLinks(int $countLinks): void
    {
        $this->countLinks = $countLinks;
    }

    public function getCountContacts(): int
    {
        return $this->countContacts;
    }

    public function setCountContacts(int $countContacts): void
    {
        $this->countContacts = $countContacts;
    }

    public function getCountEvents(): int
    {
        return $this->countEvents;
    }

    public function setCountEvents(int $countEvents): void
    {
        $this->countEvents = $countEvents;
    }

    public function getCountRadios(): int
    {
        return $this->countRadios;
    }

    public function setCountRadios(int $countRadios): void
    {
        $this->countRadios = $countRadios;
    }

    public function getCountStocks(): int
    {
        return $this->countStocks;
    }

    public function setCountStocks(int $countStocks): void
    {
        $this->countStocks = $countStocks;
    }

    public function getCountStickers(): int
    {
        return $this->countStickers;
    }

    public function setCountStickers(int $countStickers): void
    {
        $this->countStickers = $countStickers;
    }

    public function isMembersHide(): bool
    {
        return $this->membersHide;
    }

    public function setMembersHide(bool $membersHide): void
    {
        $this->membersHide = $membersHide;
    }

    public function toArray(): array
    {
        return [
            'id'                => $this->getId(),
            'type'              => $this->getType(),
            'access'            => $this->getKind(),
            'is_music'          => $this->isMusic(),
            'name'              => $this->getName(),
            'photo'             => $this->getPhoto()?->getValue(),
            'count_audio_likes' => $this->getCountAudioLikes(),
            'age_limits'        => $this->getAgeLimit(),
            'members_hide'      => $this->isMembersHide(),
        ];
    }
}
