<?php

declare(strict_types=1);

namespace App\Modules\Post\Entity\PostCommentLike;

use Doctrine\ORM\Mapping as ORM;
use DomainException;

#[ORM\Entity]
#[ORM\Table(name: 'post_comment_like')]
class PostCommentLike
{
    private const LIMIT_TOTAL = 500000;
    private const LIMIT_DAILY = 500;

    #[ORM\Id]
    #[ORM\Column(type: 'bigint', unique: true)]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private ?int $id = null;

    #[ORM\Column(type: 'bigint', length: 20, options: ['unsigned' => true])]
    private int $commentId;

    #[ORM\Column(type: 'bigint', length: 20, options: ['unsigned' => true])]
    private int $userId;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $createdAt;

    private function __construct(
        int $userId,
        int $commentId
    ) {
        $this->userId = $userId;
        $this->commentId = $commentId;

        $this->createdAt = time();
    }

    public static function create(
        int $userId,
        int $commentId
    ): self {
        return new self(
            userId: $userId,
            commentId: $commentId
        );
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

    public function getCommentId(): int
    {
        return $this->commentId;
    }

    public function setCommentId(int $commentId): void
    {
        $this->commentId = $commentId;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function setUserId(int $userId): void
    {
        $this->userId = $userId;
    }

    public function getCreatedAt(): ?int
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?int $createdAt): void
    {
        $this->createdAt = $createdAt;
    }
}
