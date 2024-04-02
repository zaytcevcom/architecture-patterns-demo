<?php

declare(strict_types=1);

namespace App\Modules\Union\Entity\UnionNotification;

use Doctrine\ORM\Mapping as ORM;
use DomainException;

#[ORM\Entity]
#[ORM\Table(name: 'union_notification')]
class UnionNotification
{
    private const LIMIT_TOTAL   = 250;
    private const LIMIT_DAILY   = 50;

    #[ORM\Id]
    #[ORM\Column(type: 'bigint', unique: true, options: ['unsigned' => true])]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private ?int $id = null;

    #[ORM\Column(type: 'integer')]
    private int $userId;

    #[ORM\Column(type: 'integer')]
    private int $unionId;

    #[ORM\Column(type: 'integer', options: ['unsigned' => true])]
    private int $time;

    public function __construct(
        int $userId,
        int $unionId
    ) {
        $this->userId = $userId;
        $this->unionId = $unionId;

        $this->time = time();
    }

    public static function create(int $userId, int $unionId): self
    {
        return new self($userId, $unionId);
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

    public function getUserID(): int
    {
        return $this->userId;
    }

    public function setUserId(int $userId): void
    {
        $this->userId = $userId;
    }

    public function getUnionId(): int
    {
        return $this->unionId;
    }

    public function setUnionId(int $unionId): void
    {
        $this->unionId = $unionId;
    }

    public function getTime(): int
    {
        return $this->time;
    }

    public function setTime(int $time): void
    {
        $this->time = $time;
    }
}
