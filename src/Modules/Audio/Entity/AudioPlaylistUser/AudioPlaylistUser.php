<?php

declare(strict_types=1);

namespace App\Modules\Audio\Entity\AudioPlaylistUser;

use App\Modules\Audio\Entity\AudioPlaylist\AudioPlaylist;
use App\Modules\Identity\Entity\User\User;
use Doctrine\ORM\Mapping as ORM;
use DomainException;

#[ORM\Entity]
#[ORM\Table(name: 'audio_playlist_user')]
class AudioPlaylistUser
{
    private const LIMIT_TOTAL = 10000;
    private const LIMIT_DAILY = 500;

    #[ORM\Id]
    #[ORM\Column(type: 'integer', unique: true)]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: AudioPlaylist::class)]
    #[ORM\JoinColumn(name: 'playlist_id', referencedColumnName: 'id', unique: false)]
    private AudioPlaylist $audioPlaylist;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', unique: false)]
    private User $user;

    #[ORM\Column(name: 'created_at', type: 'integer', nullable: true)]
    private ?int $createdAt = null;

    private function __construct(
        AudioPlaylist $audioPlaylist,
        User $user,
    ) {
        $this->audioPlaylist = $audioPlaylist;
        $this->user = $user;
    }

    public static function create(
        AudioPlaylist $audioPlaylist,
        User $user,
    ): self {
        $item = new self(
            audioPlaylist: $audioPlaylist,
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

    public function getAudioPlaylist(): AudioPlaylist
    {
        return $this->audioPlaylist;
    }

    public function setAudioPlaylist(AudioPlaylist $audioPlaylist): void
    {
        $this->audioPlaylist = $audioPlaylist;
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
