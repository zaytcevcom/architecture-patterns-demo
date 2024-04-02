<?php

declare(strict_types=1);

namespace App\Modules\Audio\Command\AudioAlbum\Remove;

use App\Modules\Audio\Command\AudioAlbum\UpdateCounter\AudioAlbumUpdateCounterAddHandler;
use App\Modules\Audio\Entity\AudioAlbum\AudioAlbumRepository;
use App\Modules\Audio\Entity\AudioAlbumUser\AudioAlbumUserRepository;
use App\Modules\Identity\Entity\User\UserRepository;
use ZayMedia\Shared\Components\Flusher;

final readonly class AudioAlbumRemoveHandler
{
    public function __construct(
        private UserRepository $userRepository,
        private AudioAlbumRepository $audioAlbumRepository,
        private AudioAlbumUserRepository $audioAlbumUserRepository,
        private AudioAlbumUpdateCounterAddHandler $audioAlbumUpdateCounterAddHandler,
        private Flusher $flusher
    ) {}

    public function handle(AudioAlbumRemoveCommand $command): void
    {
        $user       = $this->userRepository->getById($command->userId);
        $audioAlbum = $this->audioAlbumRepository->getById($command->albumId);

        $audioAlbumUser = $this->audioAlbumUserRepository->findByAudioAlbumAndUser($audioAlbum, $user);

        if (!$audioAlbumUser) {
            return;
        }

        $this->audioAlbumUserRepository->remove($audioAlbumUser);
        $this->flusher->flush();

        $this->audioAlbumUpdateCounterAddHandler->handle($audioAlbum->getId());
    }
}
