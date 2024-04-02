<?php

declare(strict_types=1);

namespace App\Modules\Audio\Command\Audio\Remove;

use App\Modules\Audio\Command\Audio\UpdateCounter\AudioUpdateCounterAddHandler;
use App\Modules\Audio\Command\AudioAlbum\UpdateCounter\AudioAlbumUpdateCounterAddHandler;
use App\Modules\Audio\Command\AudioAlbum\UpdateCounter\AudioAlbumUpdateCounterLikesHandler;
use App\Modules\Audio\Entity\Audio\AudioRepository;
use App\Modules\Audio\Entity\AudioUser\AudioUserRepository;
use App\Modules\Identity\Command\UpdateCounter\IdentityUpdateCounterAudiosHandler;
use App\Modules\Identity\Entity\User\UserRepository;
use ZayMedia\Shared\Components\Flusher;

final readonly class AudioRemoveHandler
{
    public function __construct(
        private UserRepository $userRepository,
        private AudioRepository $audioRepository,
        private AudioUserRepository $audioUserRepository,
        private AudioUpdateCounterAddHandler $audioUpdateCounterAddHandler,
        private AudioAlbumUpdateCounterAddHandler $audioAlbumUpdateCounterAddHandler,
        private AudioAlbumUpdateCounterLikesHandler $audioAlbumUpdateCounterLikesHandler,
        private IdentityUpdateCounterAudiosHandler $identityUpdateCounterAudiosHandler,
        private Flusher $flusher
    ) {}

    public function handle(AudioRemoveCommand $command): void
    {
        $user   = $this->userRepository->getById($command->userId);
        $audio  = $this->audioRepository->getById($command->audioId);

        $audioUser = $this->audioUserRepository->findByAudioAndUser($audio, $user);

        if (!$audioUser) {
            return;
        }

        $this->audioUserRepository->remove($audioUser);
        $this->flusher->flush();

        $this->identityUpdateCounterAudiosHandler->handle($user->getId());

        $this->audioUpdateCounterAddHandler->handle($audio->getId());

        // Update audio album counter add
        if ($albumId = $audio->getAlbum()?->getId()) {
            $this->audioAlbumUpdateCounterAddHandler->handle($albumId);
            $this->audioAlbumUpdateCounterLikesHandler->handle($albumId);
        }
    }
}
