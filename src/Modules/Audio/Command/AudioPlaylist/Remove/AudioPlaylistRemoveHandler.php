<?php

declare(strict_types=1);

namespace App\Modules\Audio\Command\AudioPlaylist\Remove;

use App\Modules\Audio\Command\AudioPlaylist\UpdateCounter\AudioPlaylistUpdateCounterAddHandler;
use App\Modules\Audio\Entity\AudioPlaylist\AudioPlaylistRepository;
use App\Modules\Audio\Entity\AudioPlaylistUser\AudioPlaylistUserRepository;
use App\Modules\Identity\Entity\User\UserRepository;
use ZayMedia\Shared\Components\Flusher;

final readonly class AudioPlaylistRemoveHandler
{
    public function __construct(
        private UserRepository $userRepository,
        private AudioPlaylistRepository $audioPlaylistRepository,
        private AudioPlaylistUserRepository $audioPlaylistUserRepository,
        private AudioPlaylistUpdateCounterAddHandler $audioPlaylistUpdateCounterAddHandler,
        private Flusher $flusher
    ) {}

    public function handle(AudioPlaylistRemoveCommand $command): void
    {
        $user           = $this->userRepository->getById($command->userId);
        $audioPlaylist  = $this->audioPlaylistRepository->getById($command->playlistId);

        $audioPlaylistUser = $this->audioPlaylistUserRepository->findByAudioPlaylistAndUser($audioPlaylist, $user);

        if (!$audioPlaylistUser) {
            return;
        }

        $this->audioPlaylistUserRepository->remove($audioPlaylistUser);
        $this->flusher->flush();

        $this->audioPlaylistUpdateCounterAddHandler->handle($audioPlaylist->getId());
    }
}
