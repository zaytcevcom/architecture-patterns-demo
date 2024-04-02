<?php

declare(strict_types=1);

namespace App\Modules\Audio\Command\AudioPlaylist\UpdateCounter;

use App\Modules\Audio\Entity\AudioPlaylist\AudioPlaylistRepository;
use App\Modules\Audio\Entity\AudioPlaylistUser\AudioPlaylistUserRepository;
use ZayMedia\Shared\Components\Flusher;

final readonly class AudioPlaylistUpdateCounterAddHandler
{
    public function __construct(
        private AudioPlaylistRepository $audioPlaylistRepository,
        private AudioPlaylistUserRepository $audioPlaylistUserRepository,
        private Flusher $flusher
    ) {}

    public function handle(int $id): void
    {
        $audioPlaylist = $this->audioPlaylistRepository->getById($id);

        $audioPlaylist->setCountAdd(
            $this->audioPlaylistUserRepository->countByAudioPlaylist($audioPlaylist)
        );

        $this->flusher->flush();
    }
}
