<?php

declare(strict_types=1);

namespace App\Modules\Audio\Command\AudioPlaylist\AddAudio;

use App\Modules\Audio\Command\AudioPlaylist\UpdateCounter\AudioPlaylistUpdateCounterAudiosHandler;
use App\Modules\Audio\Command\AudioPlaylist\UpdateCounter\AudioPlaylistUpdateCounterDurationHandler;
use App\Modules\Audio\Entity\Audio\AudioRepository;
use App\Modules\Audio\Entity\AudioPlaylist\AudioPlaylistRepository;
use App\Modules\Audio\Entity\AudioPlaylistAudio\AudioPlaylistAudio;
use App\Modules\Audio\Entity\AudioPlaylistAudio\AudioPlaylistAudioRepository;
use ZayMedia\Shared\Components\Flusher;

final readonly class AudioPlaylistAddAudioHandler
{
    public function __construct(
        private AudioRepository $audioRepository,
        private AudioPlaylistRepository $audioPlaylistRepository,
        private AudioPlaylistAudioRepository $audioPlaylistAudioRepository,
        private AudioPlaylistUpdateCounterDurationHandler $audioPlaylistUpdateCounterDurationHandler,
        private AudioPlaylistUpdateCounterAudiosHandler $audioPlaylistUpdateCounterAudiosHandler,
        private Flusher $flusher,
    ) {}

    public function handle(AudioPlaylistAddAudioCommand $command): bool
    {
        $audioPlaylist  = $this->audioPlaylistRepository->getById($command->playlistId);
        $audio          = $this->audioRepository->getById($command->audioId);

        if ($this->audioPlaylistAudioRepository->findByAudioPlaylistAndAudio($audioPlaylist, $audio)) {
            return false;
        }

        $audioPlaylistAudio = AudioPlaylistAudio::create(
            audioPlaylist: $audioPlaylist,
            audio: $audio
        );

        $audioPlaylist->setUpdatedAt(time());

        $this->audioPlaylistAudioRepository->add($audioPlaylistAudio);
        $this->audioPlaylistRepository->add($audioPlaylist);

        $this->flusher->flush();

        $this->audioPlaylistUpdateCounterAudiosHandler->handle($audioPlaylist->getId());
        $this->audioPlaylistUpdateCounterDurationHandler->handle($audioPlaylist->getId());

        return true;
    }
}
