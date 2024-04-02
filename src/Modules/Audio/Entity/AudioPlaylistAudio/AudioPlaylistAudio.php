<?php

declare(strict_types=1);

namespace App\Modules\Audio\Entity\AudioPlaylistAudio;

use App\Modules\Audio\Entity\Audio\Audio;
use App\Modules\Audio\Entity\AudioPlaylist\AudioPlaylist;
use Doctrine\ORM\Mapping as ORM;
use DomainException;

#[ORM\Entity]
#[ORM\Table(name: 'audio_playlist_audio')]
class AudioPlaylistAudio
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer', unique: true)]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: AudioPlaylist::class)]
    #[ORM\JoinColumn(name: 'playlist_id', referencedColumnName: 'id', unique: false)]
    private AudioPlaylist $audioPlaylist;

    #[ORM\ManyToOne(targetEntity: Audio::class)]
    #[ORM\JoinColumn(name: 'audio_id', referencedColumnName: 'id', unique: false)]
    private Audio $audio;

    #[ORM\Column(name: 'created_at', type: 'integer', nullable: true)]
    private ?int $createdAt = null;

    private function __construct(
        AudioPlaylist $audioPlaylist,
        Audio $audio,
    ) {
        $this->audioPlaylist = $audioPlaylist;
        $this->audio = $audio;
    }

    public static function create(
        AudioPlaylist $audioPlaylist,
        Audio $audio,
    ): self {
        $item = new self(
            audioPlaylist: $audioPlaylist,
            audio: $audio
        );

        $item->createdAt = time();

        return $item;
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

    public function getAudio(): Audio
    {
        return $this->audio;
    }

    public function setAudio(Audio $audio): void
    {
        $this->audio = $audio;
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
