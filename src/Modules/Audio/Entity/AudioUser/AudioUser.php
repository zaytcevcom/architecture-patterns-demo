<?php

declare(strict_types=1);

namespace App\Modules\Audio\Entity\AudioUser;

use App\Modules\Audio\Entity\Audio\Audio;
use App\Modules\Identity\Entity\User\User;
use Doctrine\ORM\Mapping as ORM;
use DomainException;

#[ORM\Entity]
#[ORM\Table(name: 'audios_owners')]
class AudioUser
{
    private const LIMIT_TOTAL = 10000;
    private const LIMIT_DAILY = 500;

    #[ORM\Id]
    #[ORM\Column(type: 'integer', unique: true)]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Audio::class)]
    #[ORM\JoinColumn(name: 'audio_id', referencedColumnName: 'id', unique: false)]
    private Audio $audio;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'owner_id', referencedColumnName: 'id', unique: false)]
    private User $user;

    #[ORM\Column(name: 'time', type: 'integer', nullable: true)]
    private ?int $createdAt = null;

    private function __construct(
        Audio $audio,
        User $user,
    ) {
        $this->audio = $audio;
        $this->user = $user;
    }

    public static function create(
        Audio $audio,
        User $user,
    ): self {
        $item = new self(
            audio: $audio,
            user: $user
        );

        $item->createdAt = time();

        return $item;
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

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): void
    {
        $this->user = $user;
    }

    public function getAudio(): Audio
    {
        return $this->audio;
    }

    public function setAudio(Audio $audio): void
    {
        $this->audio = $audio;
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
