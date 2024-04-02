<?php

declare(strict_types=1);

namespace App\Modules\Audio\Command\Audio\Add;

use App\Modules\Audio\Command\Audio\UpdateCounter\AudioUpdateCounterAddHandler;
use App\Modules\Audio\Command\AudioAlbum\UpdateCounter\AudioAlbumUpdateCounterAddHandler;
use App\Modules\Audio\Command\AudioAlbum\UpdateCounter\AudioAlbumUpdateCounterLikesHandler;
use App\Modules\Audio\Entity\Audio\Audio;
use App\Modules\Audio\Entity\Audio\AudioRepository;
use App\Modules\Audio\Entity\AudioUser\AudioUser;
use App\Modules\Audio\Entity\AudioUser\AudioUserRepository;
use App\Modules\Identity\Command\UpdateCounter\IdentityUpdateCounterAudiosHandler;
use App\Modules\Identity\Entity\User\UserRepository;
use ZayMedia\Shared\Components\Flusher;
use ZayMedia\Shared\Http\Exception\DomainExceptionModule;

final readonly class AudioAddHandler
{
    public function __construct(
        private UserRepository $userRepository,
        private AudioRepository $audioRepository,
        private AudioUserRepository $audioUserRepository,
        private AudioUpdateCounterAddHandler $audioUpdateCounterAddHandler,
        private AudioAlbumUpdateCounterAddHandler $audioAlbumUpdateCounterAddHandler,
        private AudioAlbumUpdateCounterLikesHandler $audioAlbumUpdateCounterLikesHandler,
        private IdentityUpdateCounterAudiosHandler $identityUpdateCounterAudiosHandler,
        private Flusher $flusher,
    ) {}

    public function handle(AudioAddCommand $command): void
    {
        $user   = $this->userRepository->getById($command->userId);
        $audio  = $this->audioRepository->getById($command->audioId);

        // Check max limit
        if ($this->audioUserRepository->countByUserId($user->getId()) >= Audio::limitTotal()) {
            throw new DomainExceptionModule(
                module: 'audio',
                message: 'error.audio.limit_total',
                code: 2
            );
        }

        // Check daily limit
        if ($this->audioUserRepository->countTodayByUserId($user->getId()) >= Audio::limitDaily()) {
            throw new DomainExceptionModule(
                module: 'audio',
                message: 'error.audio.limit_daily',
                code: 3
            );
        }

        if ($this->audioUserRepository->findByAudioAndUser($audio, $user)) {
            return;
        }

        $audioUser = AudioUser::create(
            audio: $audio,
            user: $user
        );

        $this->audioUserRepository->add($audioUser);
        $this->flusher->flush();

        $this->identityUpdateCounterAudiosHandler->handle($user->getId());

        // Update audio counter add
        $this->audioUpdateCounterAddHandler->handle($audio->getId());

        // Update audio album counter add
        if ($albumId = $audio->getAlbum()?->getId()) {
            $this->audioAlbumUpdateCounterAddHandler->handle($albumId);
            $this->audioAlbumUpdateCounterLikesHandler->handle($albumId);
        }
    }
}
