<?php

declare(strict_types=1);

namespace App\Modules\Union\Entity\UnionLink;

use Doctrine\ORM\Mapping as ORM;
use DomainException;

#[ORM\Entity]
#[ORM\Table(name: 'unions_links')]
class UnionLink
{
    private const LIMIT_TOTAL = 50;

    #[ORM\Id]
    #[ORM\Column(type: 'integer', unique: true)]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private ?int $id = null;

    #[ORM\Column(type: 'integer')]
    private int $unionId;

    #[ORM\Column(type: 'string', length: 500)]
    private string $url;

    #[ORM\Column(type: 'string', length: 255)]
    private string $title;

    #[ORM\Column(type: 'integer')]
    private int $createdAt;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $updatedAt;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $deletedAt;

    public function __construct(
        int $unionId,
        string $url,
        string $title,
    ) {
        $this->unionId = $unionId;
        $this->url = $url;
        $this->title = $title;
        $this->createdAt = time();
        $this->updatedAt = null;
        $this->deletedAt = null;
    }

    public static function create(
        int $unionId,
        string $url,
        string $title,
    ): self {
        return new self(
            unionId: $unionId,
            url: $url,
            title: $title,
        );
    }

    public function edit(
        string $url,
        string $title,
    ): void {
        $this->setUrl($url);
        $this->setTitle($title);
    }

    public function markDeleted(): void
    {
        $this->deletedAt = time();
    }

    public static function limitTotal(): int
    {
        return self::LIMIT_TOTAL;
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

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setUrl(string $url): void
    {
        $this->url = $url;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getCreatedAt(): int
    {
        return $this->createdAt;
    }

    public function setCreatedAt(int $createdAt): void
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
            'id'    => $this->getId(),
            'url'   => $this->getUrl(),
            'title' => $this->getTitle(),
        ];
    }
}
