<?php

declare(strict_types=1);

namespace App\Modules\Post\Entity\PostHide;

use Doctrine\ORM\Mapping as ORM;
use DomainException;

#[ORM\Entity]
#[ORM\Table(name: 'post_hide')]
class PostHide
{
    #[ORM\Id]
    #[ORM\Column(type: 'bigint', unique: true, options: ['unsigned' => true])]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private ?int $id = null;

    #[ORM\Column(type: 'bigint', options: ['unsigned' => true])]
    private int $postId;

    #[ORM\Column(type: 'bigint', options: ['unsigned' => true])]
    private int $userId;

    #[ORM\Column(type: 'integer', options: ['unsigned' => true])]
    private int $time;

    private function __construct(
        int $postId,
        int $userId
    ) {
        $this->postId = $postId;
        $this->userId = $userId;

        $this->time = time();
    }

    public static function create(int $postId, int $userId): self
    {
        return new self(
            postId: $postId,
            userId: $userId
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

    public function getTime(): int
    {
        return $this->time;
    }
}
