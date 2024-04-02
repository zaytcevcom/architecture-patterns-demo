<?php

declare(strict_types=1);

namespace App\Modules\Identity\Command\UpdateCounter;

use App\Modules\Audio\Entity\AudioUser\AudioUserRepository;
use App\Modules\Identity\Entity\User\UserRepository;
use ZayMedia\Shared\Components\Flusher;

final readonly class IdentityUpdateCounterAudiosHandler
{
    public function __construct(
        private UserRepository $userRepository,
        private AudioUserRepository $audioUserRepository,
        private Flusher $flusher
    ) {}

    public function handle(int $id): void
    {
        $user = $this->userRepository->getById($id);

        $user->setCountAudios(
            $this->audioUserRepository->countByUserId($user->getId())
        );

        $this->flusher->flush();
    }
}
