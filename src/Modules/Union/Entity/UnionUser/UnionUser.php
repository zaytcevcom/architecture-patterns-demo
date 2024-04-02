<?php

declare(strict_types=1);

namespace App\Modules\Union\Entity\UnionUser;

use App\Modules\Union\Entity\Union\Union;
use Doctrine\ORM\Mapping as ORM;
use DomainException;

#[ORM\Entity]
#[ORM\Table(name: 'unions_users')]
class UnionUser
{
    private const LIMIT_TOTAL   = 10000;
    private const LIMIT_DAILY   = 50;

    private const LIMIT_INVITE_TOTAL   = 5000;
    private const LIMIT_INVITE_DAILY   = 50;

    #[ORM\Id]
    #[ORM\Column(type: 'integer', unique: true)]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private ?int $id = null;

    #[ORM\Column(type: 'integer')]
    private int $unionId;

    #[ORM\Column(type: 'integer')]
    private int $userId;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $role = 0;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $views = 0;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $lastView = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $timeJoin = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $invitedBy = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $timeInvite = null;

    private function __construct(
        int $userId,
        int $unionId,
    ) {
        $this->unionId = $unionId;
        $this->userId = $userId;
    }

    public static function joinCreator(
        int $userId,
        int $unionId
    ): self {
        $unionUser = new self($userId, $unionId);

        $unionUser->setRole(Union::roleCreator());

        $unionUser->lastView = time();
        $unionUser->timeJoin = time();

        return $unionUser;
    }

    public static function join(
        int $userId,
        int $unionId
    ): self {
        $unionUser = new self($userId, $unionId);

        $unionUser->setRole(Union::roleMember());

        $unionUser->lastView = time();
        $unionUser->timeJoin = time();

        return $unionUser;
    }

    public static function sendRequest(
        int $userId,
        int $unionId
    ): self {
        $unionUser = new self($userId, $unionId);

        $unionUser->setRole(Union::roleRequest());

        return $unionUser;
    }

    public static function invite(
        int $sourceId,
        int $targetId,
        int $unionId
    ): self {
        $unionUser = new self($targetId, $unionId);

        $unionUser->setRole(Union::roleInvite());
        $unionUser->setInvitedBy($sourceId);
        $unionUser->setTimeInvite(time());

        return $unionUser;
    }

    public function acceptInvite(): void
    {
        $this->setRole(Union::roleMember());
        $this->lastView = time();
        $this->timeJoin = time();
    }

    public static function limitTotal(): int
    {
        return self::LIMIT_TOTAL;
    }

    public static function limitDaily(): int
    {
        return self::LIMIT_DAILY;
    }

    public static function limitInviteTotal(): int
    {
        return self::LIMIT_INVITE_TOTAL;
    }

    public static function limitInviteDaily(): int
    {
        return self::LIMIT_INVITE_DAILY;
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

    public function getRole(): int
    {
        return $this->role;
    }

    public function setRole(int $role): void
    {
        $this->role = $role;
    }

    public function getViews(): int
    {
        return $this->views;
    }

    public function setViews(int $views): void
    {
        $this->views = $views;
    }

    public function getLastView(): ?int
    {
        return $this->lastView;
    }

    public function setLastView(int $lastView): void
    {
        $this->lastView = $lastView;
    }

    public function getTimeJoin(): ?int
    {
        return $this->timeJoin;
    }

    public function setTimeJoin(int $timeJoin): void
    {
        $this->timeJoin = $timeJoin;
    }

    public function getInvitedBy(): ?int
    {
        return $this->invitedBy;
    }

    public function setInvitedBy(?int $invitedBy): void
    {
        $this->invitedBy = $invitedBy;
    }

    public function getTimeInvite(): ?int
    {
        return $this->timeInvite;
    }

    public function setTimeInvite(?int $timeInvite): void
    {
        $this->timeInvite = $timeInvite;
    }

    public function toArray(): array
    {
        return [
            'id'       => $this->getId(),
            'user_id'  => $this->getUserId(),
            'role'     => $this->getRole(),
        ];
    }
}
