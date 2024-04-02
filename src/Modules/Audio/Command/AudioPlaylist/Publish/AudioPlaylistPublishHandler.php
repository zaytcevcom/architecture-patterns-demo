<?php

declare(strict_types=1);

namespace App\Modules\Audio\Command\AudioPlaylist\Publish;

use App\Modules\Audio\Entity\AudioPlaylist\AudioPlaylistRepository;
use ZayMedia\Shared\Components\Flusher;

final readonly class AudioPlaylistPublishHandler
{
    public function __construct(
        private AudioPlaylistRepository $audioPlaylistRepository,
        private Flusher $flusher
    ) {}

    public function handle(AudioPlaylistPublishCommand $command): void
    {
        $audioPlaylist = $this->audioPlaylistRepository->getById($command->playlistId);

        // todo: проверка прав доступа $command->userId

        $time = ($command->time < time()) ? time() : $command->time;

        $audioPlaylist->publish($time);

        $this->audioPlaylistRepository->add($audioPlaylist);
        $this->flusher->flush();
    }
}
