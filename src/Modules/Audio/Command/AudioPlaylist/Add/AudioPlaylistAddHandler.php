<?php

declare(strict_types=1);

namespace App\Modules\Audio\Command\AudioPlaylist\Add;

use App\Modules\Audio\Command\AudioPlaylist\UpdateCounter\AudioPlaylistUpdateCounterAddHandler;
use App\Modules\Audio\Entity\AudioPlaylist\AudioPlaylist;
use App\Modules\Audio\Entity\AudioPlaylist\AudioPlaylistRepository;
use App\Modules\Audio\Entity\AudioPlaylistUser\AudioPlaylistUser;
use App\Modules\Audio\Entity\AudioPlaylistUser\AudioPlaylistUserRepository;
use App\Modules\Identity\Entity\User\UserRepository;
use ZayMedia\Shared\Components\Flusher;
use ZayMedia\Shared\Http\Exception\DomainExceptionModule;

final readonly class AudioPlaylistAddHandler
{
    public function __construct(
        private UserRepository $userRepository,
        private AudioPlaylistRepository $audioPlaylistRepository,
        private AudioPlaylistUserRepository $audioPlaylistUserRepository,
        private AudioPlaylistUpdateCounterAddHandler $audioPlaylistUpdateCounterAddHandler,
        private Flusher $flusher
    ) {}

    public function handle(AudioPlaylistAddCommand $command): void
    {
        $user           = $this->userRepository->getById($command->userId);
        $audioPlaylist  = $this->audioPlaylistRepository->getById($command->playlistId);

        // Check max limit
        if ($this->audioPlaylistUserRepository->countByUserId($user->getId()) >= AudioPlaylist::limitTotal()) {
            throw new DomainExceptionModule(
                module: 'audio',
                message: 'error.audio_playlist.limit_total',
                code: 2
            );
        }

        // Check daily limit
        if ($this->audioPlaylistUserRepository->countTodayByUserId($user->getId()) >= AudioPlaylist::limitDaily()) {
            throw new DomainExceptionModule(
                module: 'audio',
                message: 'error.audio_playlist.limit_daily',
                code: 3
            );
        }

        if ($this->audioPlaylistUserRepository->findByAudioPlaylistAndUser($audioPlaylist, $user)) {
            return;
        }

        $audioPlaylistUser = AudioPlaylistUser::create(
            audioPlaylist: $audioPlaylist,
            user: $user
        );

        $this->audioPlaylistUserRepository->add($audioPlaylistUser);
        $this->flusher->flush();

        $this->audioPlaylistUpdateCounterAddHandler->handle($audioPlaylist->getId());
    }
}
