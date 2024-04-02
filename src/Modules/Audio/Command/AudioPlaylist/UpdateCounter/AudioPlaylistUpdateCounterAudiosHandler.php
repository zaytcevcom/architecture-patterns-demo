<?php

declare(strict_types=1);

namespace App\Modules\Audio\Command\AudioPlaylist\UpdateCounter;

use App\Modules\Audio\Entity\AudioPlaylist\AudioPlaylistRepository;
use App\Modules\Audio\Entity\AudioPlaylistAudio\AudioPlaylistAudioRepository;
use ZayMedia\Shared\Components\Flusher;

final readonly class AudioPlaylistUpdateCounterAudiosHandler
{
    public function __construct(
        private AudioPlaylistRepository $audioPlaylistRepository,
        private AudioPlaylistAudioRepository $audioPlaylistAudioRepository,
        private Flusher $flusher
    ) {}

    public function handle(int $id): void
    {
        $audioPlaylist = $this->audioPlaylistRepository->getById($id);

        $audioPlaylist->setCountAudio(
            $this->audioPlaylistAudioRepository->countByAudioPlaylist($audioPlaylist)
        );

        $this->flusher->flush();
    }
}
