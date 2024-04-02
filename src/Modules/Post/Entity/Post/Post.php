<?php

declare(strict_types=1);

namespace App\Modules\Post\Entity\Post;

use Doctrine\ORM\Mapping as ORM;
use DomainException;

#[ORM\Entity]
#[ORM\Table(name: 'wall')]
#[ORM\Index(fields: ['ownerId'], name: 'IDX_OWNER')]
#[ORM\Index(fields: ['userId'], name: 'IDX_USER')]
#[ORM\Index(fields: ['ownerId', 'date', 'id', 'hide', 'deletedAt'], name: 'IDX_FEED')]
class Post
{
    private const LIMIT_TOTAL       = 10000000;
    private const LIMIT_DAILY       = 1000000;
    private const TIME_TO_RESTORE   = 24 * 60 * 60;
    private const TIME_TO_EDIT      = 24 * 60 * 60;

    #[ORM\Id]
    #[ORM\Column(type: 'bigint', unique: true, options: ['unsigned' => true])]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 200, nullable: true)]
    private ?string $uniqueId = null;

    #[ORM\Column(type: 'integer')]
    private int $uniqueTime;

    #[ORM\Column(type: 'bigint', length: 20)]
    private int $ownerId;

    #[ORM\Column(type: 'bigint', length: 20, options: ['unsigned' => true])]
    private int $userId;

    #[ORM\Column(type: 'bigint', length: 20, nullable: true, options: ['unsigned' => true])]
    private ?int $postId = null;

    #[ORM\Column(type: 'bigint', length: 20, nullable: true, options: ['unsigned' => true])]
    private ?int $signerId = null;

    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    private bool $signed = false;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $message;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $hashtags = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $hashtagsTime = null;

    #[ORM\Column(type: 'bigint', length: 20, nullable: true, options: ['unsigned' => true])]
    private ?int $attachmentId = null;

    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    private ?string $repost = null;

    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    private bool $isPinned = false;

    #[ORM\Column(type: 'bigint', length: 20, options: ['unsigned' => true, 'default' => 0])]
    private int $countLikes = 0;

    #[ORM\Column(type: 'bigint', length: 20, options: ['unsigned' => true, 'default' => 0])]
    private int $countReposts = 0;

    #[ORM\Column(type: 'bigint', length: 20, options: ['unsigned' => true, 'default' => 0])]
    private int $countComments = 0;

    #[ORM\Column(type: 'bigint', length: 20, options: ['unsigned' => true, 'default' => 0])]
    private int $countViews = 0;

    #[ORM\Column(type: 'bigint', length: 20, options: ['unsigned' => true, 'default' => 0])]
    private int $countViewsCheat = 0;

    #[ORM\Column(type: 'integer')]
    private int $ageLimits = 0;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $ageLimitsTime = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $createdAt;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $updatedAt;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $deletedAt;

    #[ORM\Column(type: 'integer')]
    private int $date;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $hide = 0;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $photoIds = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $photoAlbumIds = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $audioIds = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $audioAlbumIds = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $videoIds = null;

    #[ORM\Column(type: 'bigint', length: 20, nullable: true, options: ['unsigned' => true])]
    private ?int $flowId = null;

    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    private bool $closeComments;

    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    private bool $contactsOnly;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $isParsed = null;

    private function __construct(
        int $userId,
        ?int $unionId,
        ?string $message,
        ?int $uniqueTime = null,
        bool $closeComments = false,
        bool $contactsOnly = false,
    ) {
        $this->userId = $userId;
        $this->message = $message;
        $this->uniqueTime = $uniqueTime ?? time();
        $this->closeComments = $closeComments;
        $this->contactsOnly = $contactsOnly;

        $this->createdAt = time();
        $this->updatedAt = null;
        $this->deletedAt = null;

        $this->ownerId = !empty($unionId) ? -1 * $unionId : $this->userId;
        $this->date = $this->createdAt;
    }

    /**
     * @param int[]|null $photoIds
     * @param int[]|null $audioIds
     * @param int[]|null $videoIds
     */
    public static function create(
        int $userId,
        ?int $unionId,
        ?string $message,
        ?array $photoIds = null,
        ?array $audioIds = null,
        ?array $videoIds = null,
        ?int $flowId = null,
        ?int $time = null,
        ?int $uniqueTime = null,
        bool $closeComments = false,
        bool $contactsOnly = false,
    ): self {
        $post = new self(
            userId: $userId,
            unionId: $unionId,
            message: $message,
            uniqueTime: $uniqueTime,
            closeComments: $closeComments,
            contactsOnly: $contactsOnly
        );

        if (!empty($time) && $time > time()) {
            $post->setDate($time);
        } else {
            $post->setDate(time());
        }

        $post->setPhotoIds($photoIds);
        $post->setAudioIds($audioIds);
        $post->setVideoIds($videoIds);
        $post->setFlowId($flowId);

        return $post;
    }

    public static function repost(
        int $userId,
        ?int $unionId,
        int $postId,
        ?string $message,
        ?int $time = null,
        ?int $uniqueTime = null,
        bool $closeComments = false,
        bool $contactsOnly = false,
    ): self {
        $post = new self(
            userId: $userId,
            unionId: $unionId,
            message: $message,
            uniqueTime: $uniqueTime ?? time(),
            closeComments: $closeComments,
            contactsOnly: $contactsOnly
        );

        if (!empty($time) && $time > time()) {
            $post->setDate($time);
        } else {
            $post->setDate(time());
        }

        $post->setPostId($postId);

        return $post;
    }

    public function canEdit(): bool
    {
        if ($this->date < time()) {
            return true;
        }

        return time() - $this->date < self::TIME_TO_EDIT;
    }

    /**
     * @param int[]|null $photoIds
     * @param int[]|null $audioIds
     * @param int[]|null $videoIds
     */
    public function edit(
        ?string $message,
        ?array $photoIds,
        ?array $audioIds,
        ?array $videoIds,
        ?int $time,
        bool $closeComments,
        bool $contactsOnly,
    ): void {
        $this->message = $message;

        $this->setPhotoIds($photoIds);
        $this->setAudioIds($audioIds);
        $this->setVideoIds($videoIds);

        if ($this->isPostponed()) {
            if (!empty($time) && $time > time()) {
                $this->setDate($time);
            } else {
                $this->setDate(time());
            }
        }

        $this->closeComments = $closeComments;
        $this->contactsOnly = $contactsOnly;

        $this->updatedAt = time();
    }

    public function publish(): void
    {
        $this->setDate(time());
    }

    public function pin(): void
    {
        $this->isPinned = true;
    }

    public function setCloseComments(bool $closeComments): void
    {
        $this->closeComments = $closeComments;
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

    public static function getAppUrl(int $id): string
    {
        return 'lo://posts/' . $id;
    }

    public static function getWebUrl(int $owner_id, int $id): string
    {
        return 'https://lo.ink/wall_post/' . $owner_id . '_' . $id;
    }

    public static function limitTotal(): int
    {
        return self::LIMIT_TOTAL;
    }

    public static function limitDaily(): int
    {
        return self::LIMIT_DAILY;
    }

    public function isPostponed(): bool
    {
        return $this->getDate() > time();
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

    public function getUniqueId(): ?string
    {
        return $this->uniqueId;
    }

    public function setUniqueId(?string $uniqueId): void
    {
        $this->uniqueId = $uniqueId;
    }

    public function getUniqueTime(): int
    {
        return $this->uniqueTime;
    }

    public function setUniqueTime(int $uniqueTime): void
    {
        $this->uniqueTime = $uniqueTime;
    }

    public function getOwnerId(): int
    {
        return $this->ownerId;
    }

    public function setOwnerId(int $ownerId): void
    {
        $this->ownerId = $ownerId;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function setUserId(int $userId): void
    {
        $this->userId = $userId;
    }

    public function getUnionId(): ?int
    {
        return ($this->ownerId < 0) ? -1 * $this->ownerId : null;
    }

    public function getPostId(): ?int
    {
        return $this->postId;
    }

    public function setPostId(?int $postId): void
    {
        $this->postId = $postId;
    }

    public function getSignerId(): ?int
    {
        return $this->signerId;
    }

    public function setSignerId(?int $signerId): void
    {
        $this->signerId = $signerId;
    }

    public function isSigned(): bool
    {
        return $this->signed;
    }

    public function setSigned(bool $signed): void
    {
        $this->signed = $signed;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(?string $message): void
    {
        $this->message = $message;
    }

    public function getHashtags(): ?string
    {
        return $this->hashtags;
    }

    public function setHashtags(?string $hashtags): void
    {
        $this->hashtags = $hashtags;
    }

    public function getHashtagsTime(): ?int
    {
        return $this->hashtagsTime;
    }

    public function setHashtagsTime(?int $hashtagsTime): void
    {
        $this->hashtagsTime = $hashtagsTime;
    }

    public function getAttachmentId(): ?int
    {
        return $this->attachmentId;
    }

    public function setAttachmentId(?int $attachmentId): void
    {
        $this->attachmentId = $attachmentId;
    }

    public function getRepost(): ?string
    {
        return $this->repost;
    }

    public function setRepost(?string $repost): void
    {
        $this->repost = $repost;
    }

    public function isPinned(): bool
    {
        return $this->isPinned;
    }

    public function setIsPinned(bool $isPinned): void
    {
        $this->isPinned = $isPinned;
    }

    public function getCountLikes(): int
    {
        return $this->countLikes;
    }

    public function setCountLikes(int $countLikes): void
    {
        $this->countLikes = $countLikes;
    }

    public function getCountReposts(): int
    {
        return $this->countReposts;
    }

    public function setCountReposts(int $countReposts): void
    {
        $this->countReposts = $countReposts;
    }

    public function getCountComments(): int
    {
        return $this->countComments;
    }

    public function setCountComments(int $countComments): void
    {
        $this->countComments = $countComments;
    }

    public function getCountViews(): int
    {
        return $this->countViews;
    }

    public function setCountViews(int $countViews): void
    {
        $this->countViews = $countViews;
    }

    public function getCountViewsCheat(): int
    {
        return $this->countViewsCheat;
    }

    public function setCountViewsCheat(int $countViewsCheat): void
    {
        $this->countViewsCheat = $countViewsCheat;
    }

    public function getAgeLimits(): int
    {
        return $this->ageLimits;
    }

    public function setAgeLimits(int $ageLimits): void
    {
        $this->ageLimits = $ageLimits;
    }

    public function getAgeLimitsTime(): ?int
    {
        return $this->ageLimitsTime;
    }

    public function setAgeLimitsTime(?int $ageLimitsTime): void
    {
        $this->ageLimitsTime = $ageLimitsTime;
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

    public function getDate(): int
    {
        return $this->date;
    }

    public function setDate(int $date): void
    {
        $this->date = $date;
    }

    public function getHide(): int
    {
        return $this->hide;
    }

    public function setHide(int $hide): void
    {
        $this->hide = $hide;
    }

    public function getPhotoIds(): ?string
    {
        return $this->photoIds;
    }

    /** @param int[] $photoIds */
    public function setPhotoIds(?array $photoIds): void
    {
        $this->photoIds = (!empty($photoIds)) ? implode(',', array_unique($photoIds)) : null;
    }

    public function getPhotoAlbumIds(): ?string
    {
        return $this->photoAlbumIds;
    }

    public function setPhotoAlbumIds(?string $photoAlbumIds): void
    {
        $this->photoAlbumIds = $photoAlbumIds;
    }

    public function getAudioIds(): ?string
    {
        return $this->audioIds;
    }

    /** @param int[] $audioIds */
    public function setAudioIds(?array $audioIds): void
    {
        $this->audioIds = (!empty($audioIds)) ? implode(',', array_unique($audioIds)) : null;
    }

    public function getAudioAlbumIds(): ?string
    {
        return $this->audioAlbumIds;
    }

    public function setAudioAlbumIds(?string $audioAlbumIds): void
    {
        $this->audioAlbumIds = $audioAlbumIds;
    }

    public function getVideoIds(): ?string
    {
        return $this->videoIds;
    }

    /** @param int[] $videoIds */
    public function setVideoIds(?array $videoIds): void
    {
        $this->videoIds = (!empty($videoIds)) ? implode(',', array_unique($videoIds)) : null;
    }

    public function getFlowId(): ?int
    {
        return $this->flowId;
    }

    public function setFlowId(?int $flowId): void
    {
        $this->flowId = $flowId;
    }

    public function getIsParsed(): ?int
    {
        return $this->isParsed;
    }

    public function setIsParsed(?int $isParsed): void
    {
        $this->isParsed = $isParsed;
    }

    public function isCloseComments(): bool
    {
        return $this->closeComments;
    }

    public function isContactsOnly(): bool
    {
        return $this->contactsOnly;
    }

    public function toArray(): array
    {
        return [
            'id'                => $this->getId(),
            'user_id'           => $this->getUserId(),
            'owner_id'          => $this->getOwnerId(),
            'post_id'           => $this->getPostId(),
            'flow_id'           => $this->getFlowId(),
            'message'           => $this->getMessage(),
            'date'              => $this->getDate(),
            'count_likes'       => $this->getCountLikes(),
            'count_comments'    => $this->getCountComments(),
            'count_reposts'     => $this->getCountReposts(),
            'count_views'       => $this->getCountViews(),
            'count_views_cheat' => $this->getCountViewsCheat(),
            'photo_ids'         => $this->getPhotoIds(),
            'audio_ids'         => $this->getAudioIds(),
            'video_ids'         => $this->getVideoIds(),
            'close_comments'    => $this->isCloseComments(),
            'contacts_only'     => $this->isContactsOnly(),
            'is_pinned'         => $this->isPinned(),
        ];
    }
}
