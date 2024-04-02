<?php

declare(strict_types=1);

namespace App\Modules\Union\Entity\UnionContact;

use Doctrine\ORM\Mapping as ORM;
use DomainException;

#[ORM\Entity]
#[ORM\Table(name: 'unions_contacts')]
class UnionContact
{
    private const LIMIT_TOTAL = 50;

    #[ORM\Id]
    #[ORM\Column(type: 'integer', unique: true)]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private ?int $id = null;

    #[ORM\Column(type: 'integer')]
    private int $unionId;

    #[ORM\Column(name: 'owner_id', type: 'integer')]
    private int $userId;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $position;

    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    private ?string $email;

    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    private ?string $phone;

    #[ORM\Column(type: 'integer')]
    private int $createdAt;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $updatedAt;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $deletedAt;

    public function __construct(
        int $unionId,
        int $userId,
        ?string $position,
        ?string $email,
        ?string $phone
    ) {
        $this->unionId = $unionId;
        $this->userId = $userId;
        $this->position = $position;
        $this->email = $email;
        $this->phone = $phone;
        $this->createdAt = time();
        $this->updatedAt = null;
        $this->deletedAt = null;
    }

    public static function create(
        int $unionId,
        int $userId,
        ?string $position,
        ?string $email,
        ?string $phone
    ): self {
        return new self(
            unionId: $unionId,
            userId: $userId,
            position: $position,
            email: $email,
            phone: $phone
        );
    }

    public function edit(
        int $userId,
        ?string $position,
        ?string $email,
        ?string $phone
    ): void {
        $this->setUserId($userId);
        $this->setPosition($position);
        $this->setEmail($email);
        $this->setPhone($phone);
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

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function setUserId(int $userId): void
    {
        $this->userId = $userId;
    }

    public function getPosition(): ?string
    {
        return $this->position;
    }

    public function setPosition(?string $position): void
    {
        $this->position = $position;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): void
    {
        $this->email = $email;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): void
    {
        $this->phone = $phone;
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
            'id'       => $this->getId(),
            'user_id'  => $this->getUserId(),
            'position' => $this->getPosition(),
            'phone'    => $this->getPhone(),
            'email'    => $this->getEmail(),
        ];
    }
}
