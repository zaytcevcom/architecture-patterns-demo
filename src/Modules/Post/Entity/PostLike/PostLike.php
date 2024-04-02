<?php

declare(strict_types=1);

namespace App\Modules\Post\Entity\PostLike;

use Doctrine\ORM\Mapping as ORM;
use DomainException;

#[ORM\Entity]
#[ORM\Table(name: 'post_like')]
#[ORM\Index(columns: ['user_id', 'post_id'], name: 'IDX_SEARCH')]
#[ORM\UniqueConstraint(name: 'IDX_UNIQUE', columns: ['user_id', 'post_id'])]
class PostLike
{
    private const LIMIT_TOTAL = 500000;
    private const LIMIT_DAILY = 500;

    #[ORM\Id]
    #[ORM\Column(type: 'bigint', unique: true)]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private ?int $id = null;

    #[ORM\Column(type: 'bigint', length: 20, options: ['unsigned' => true])]
    private int $postId;

    #[ORM\Column(type: 'bigint', length: 20, options: ['unsigned' => true])]
    private int $userId;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $createdAt;

    private function __construct(
        int $userId,
        int $postId
    ) {
        $this->userId = $userId;
        $this->postId = $postId;

        $this->createdAt = time();
    }

    public static function create(
        int $userId,
        int $postId
    ): self {
        return new self(
            userId: $userId,
            postId: $postId
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

    public function getPostId(): int
    {
        return $this->postId;
    }

    public function setPostId(int $postId): void
    {
        $this->postId = $postId;
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
