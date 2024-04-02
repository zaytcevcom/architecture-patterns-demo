<?php

declare(strict_types=1);

namespace App\Modules\Post\Entity\PostComment;

use Doctrine\ORM\Mapping as ORM;
use DomainException;

#[ORM\Entity]
#[ORM\Table(name: 'post_comment')]
#[ORM\Index(fields: ['postId'], name: 'IDX_POST')]
#[ORM\Index(fields: ['unionId'], name: 'IDX_UNION')]
#[ORM\Index(fields: ['userId'], name: 'IDX_USER')]
class PostComment
{
    private const LIMIT_TOTAL       = 500000;
    private const LIMIT_DAILY       = 1500;
    private const TIME_TO_RESTORE   = 24 * 60 * 60;

    #[ORM\Id]
    #[ORM\Column(type: 'bigint', unique: true, options: ['unsigned' => true])]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 200, nullable: true)]
    private ?string $uniqueId = null;

    #[ORM\Column(type: 'integer')]
    private int $uniqueTime;

    #[ORM\Column(type: 'bigint', length: 20, options: ['unsigned' => true])]
    private int $postId;

    #[ORM\Column(type: 'bigint', length: 20, nullable: true, options: ['unsigned' => true])]
    private ?int $commentId;

    #[ORM\Column(type: 'bigint', length: 20, nullable: true)]
    private ?int $unionId;

    #[ORM\Column(type: 'bigint', length: 20, options: ['unsigned' => true])]
    private int $userId;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $message;

    #[ORM\Column(type: 'bigint', length: 20, options: ['unsigned' => true, 'default' => 0])]
    private int $countLikes = 0;

    #[ORM\Column(type: 'bigint', length: 20, options: ['unsigned' => true, 'default' => 0])]
    private int $countComments = 0;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $createdAt;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $updatedAt;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $deletedAt;

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
    private ?int $stickerId = null;

    private function __construct(
        int $postId,
        int $userId,
        ?int $unionId,
        ?int $commentId,
        ?string $message = null,
        ?int $uniqueTime = null,
    ) {
        $this->postId = $postId;
        $this->userId = $userId;
        $this->unionId = $unionId;
        $this->commentId = $commentId;
        $this->message = $message;

        $this->uniqueTime = $uniqueTime ?? time();

        $this->createdAt = time();
        $this->updatedAt = time();
        $this->deletedAt = null;
    }

    /**
     * @param int[]|null $photoIds
     * @param int[]|null $audioIds
     * @param int[]|null $videoIds
     */
    public static function create(
        int $postId,
        int $userId,
        ?int $unionId,
        ?int $commentId = null,
        ?string $message = null,
        ?array $photoIds = null,
        ?array $audioIds = null,
        ?array $videoIds = null,
        ?int $stickerId = null,
        ?int $uniqueTime = null,
    ): self {
        $comment = new self(
            postId: $postId,
            userId: $userId,
            unionId: $unionId,
            commentId: $commentId,
            message: $message,
            uniqueTime: $uniqueTime
        );

        $comment->setPhotoIds($photoIds);
        $comment->setAudioIds($audioIds);
        $comment->setVideoIds($videoIds);

        $comment->setStickerId($stickerId);

        return $comment;
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
        ?int $stickerId = null,
    ): void {
        $this->message = $message;

        $this->setPhotoIds($photoIds);
        $this->setAudioIds($audioIds);
        $this->setVideoIds($videoIds);

        $this->setStickerId($stickerId);

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

    public function getPostId(): int
    {
        return $this->postId;
    }

    public function setPostId(int $postId): void
    {
        $this->postId = $postId;
    }

    public function getCommentId(): ?int
    {
        return $this->commentId;
    }

    public function setCommentId(?int $commentId): void
    {
        $this->commentId = $commentId;
    }

    public function getUnionId(): ?int
    {
        return $this->unionId;
    }

    public function setUnionId(?int $unionId): void
    {
        $this->unionId = $unionId;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function setUserId(int $userId): void
    {
        $this->userId = $userId;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(?string $message): void
    {
        $this->message = $message;
    }

    public function getCountLikes(): int
    {
        return $this->countLikes;
    }

    public function setCountLikes(int $countLikes): void
    {
        $this->countLikes = $countLikes;
    }

    public function getCountComments(): int
    {
        return $this->countComments;
    }

    public function setCountComments(int $countComments): void
    {
        $this->countComments = $countComments;
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

    public function getStickerId(): ?int
    {
        return $this->stickerId;
    }

    public function setStickerId(?int $stickerId): void
    {
        $this->stickerId = $stickerId;
    }

    public function toArray(): array
    {
        return [
            'id'                => $this->getId(),
            'post_id'           => $this->getPostId(),
            'comment_id'        => $this->getCommentId(),
            'user_id'           => $this->getUserId(),
            'union_id'          => $this->getUnionId(),
            'message'           => $this->getMessage(),
            'created_at'        => $this->getCreatedAt(),
            'count_likes'       => $this->getCountLikes(),
            'count_comments'    => $this->getCountComments(),
            'photo_ids'         => $this->getPhotoIds(),
            'audio_ids'         => $this->getAudioIds(),
            'video_ids'         => $this->getVideoIds(),
        ];
    }
}
