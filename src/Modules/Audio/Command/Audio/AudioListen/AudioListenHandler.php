<?php

declare(strict_types=1);

namespace App\Modules\Audio\Command\Audio\AudioListen;

use App\Modules\Audio\Entity\Audio\AudioRepository;
use App\Modules\Audio\Entity\AudioListen\AudioListen;
use App\Modules\Audio\Entity\AudioListen\AudioListenRepository;
use App\Modules\Identity\Entity\User\UserRepository;
use ZayMedia\Shared\Components\Flusher;

final readonly class AudioListenHandler
{
    public function __construct(
        private UserRepository $userRepository,
        private AudioRepository $audioRepository,
        private AudioListenRepository $audioListenRepository,
        private Flusher $flusher
    ) {}

    public function handle(AudioListenCommand $command): void
    {
        $audio = $this->audioRepository->getById($command->audioId);
        $user = $this->userRepository->getById($command->userId);

        $listen = AudioListen::create(
            audioId: $audio->getId(),
            userId: $user->getId(),
            time: time()
        );

        $this->audioListenRepository->add($listen);

        $this->flusher->flush();
    }
}
