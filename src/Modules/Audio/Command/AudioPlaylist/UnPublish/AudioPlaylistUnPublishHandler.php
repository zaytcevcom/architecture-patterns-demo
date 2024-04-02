<?php

declare(strict_types=1);

namespace App\Modules\Audio\Command\AudioPlaylist\UnPublish;

use App\Modules\Audio\Entity\AudioPlaylist\AudioPlaylistRepository;
use ZayMedia\Shared\Components\Flusher;

final readonly class AudioPlaylistUnPublishHandler
{
    public function __construct(
        private AudioPlaylistRepository $audioPlaylistRepository,
        private Flusher $flusher
    ) {}

    public function handle(AudioPlaylistUnPublishCommand $command): void
    {
        $audioPlaylist = $this->audioPlaylistRepository->getById($command->playlistId);

        // todo: проверка прав доступа $command->userId

        $audioPlaylist->unPublish();

        $this->audioPlaylistRepository->add($audioPlaylist);
        $this->flusher->flush();
    }
}
