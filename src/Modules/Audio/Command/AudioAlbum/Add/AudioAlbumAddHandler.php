<?php

declare(strict_types=1);

namespace App\Modules\Audio\Command\AudioAlbum\Add;

use App\Modules\Audio\Command\AudioAlbum\UpdateCounter\AudioAlbumUpdateCounterAddHandler;
use App\Modules\Audio\Entity\AudioAlbum\AudioAlbum;
use App\Modules\Audio\Entity\AudioAlbum\AudioAlbumRepository;
use App\Modules\Audio\Entity\AudioAlbumUser\AudioAlbumUser;
use App\Modules\Audio\Entity\AudioAlbumUser\AudioAlbumUserRepository;
use App\Modules\Identity\Entity\User\UserRepository;
use ZayMedia\Shared\Components\Flusher;
use ZayMedia\Shared\Http\Exception\DomainExceptionModule;

final readonly class AudioAlbumAddHandler
{
    public function __construct(
        private UserRepository $userRepository,
        private AudioAlbumRepository $audioAlbumRepository,
        private AudioAlbumUserRepository $audioAlbumUserRepository,
        private AudioAlbumUpdateCounterAddHandler $audioAlbumUpdateCounterAddHandler,
        private Flusher $flusher
    ) {}

    public function handle(AudioAlbumAddCommand $command): void
    {
        $user       = $this->userRepository->getById($command->userId);
        $audioAlbum = $this->audioAlbumRepository->getById($command->albumId);

        // Check max limit
        if ($this->audioAlbumUserRepository->countByUserId($user->getId()) >= AudioAlbum::limitTotal()) {
            throw new DomainExceptionModule(
                module: 'audio',
                message: 'error.audio_album.limit_total',
                code: 2
            );
        }

        // Check daily limit
        if ($this->audioAlbumUserRepository->countTodayByUserId($user->getId()) >= AudioAlbum::limitDaily()) {
            throw new DomainExceptionModule(
                module: 'audio',
                message: 'error.audio_album.limit_daily',
                code: 3
            );
        }

        if ($this->audioAlbumUserRepository->findByAudioAlbumAndUser($audioAlbum, $user)) {
            return;
        }

        $audioAlbumUser = AudioAlbumUser::create(
            audioAlbum: $audioAlbum,
            user: $user
        );

        $this->audioAlbumUserRepository->add($audioAlbumUser);
        $this->flusher->flush();

        $this->audioAlbumUpdateCounterAddHandler->handle($audioAlbum->getId());
    }
}
