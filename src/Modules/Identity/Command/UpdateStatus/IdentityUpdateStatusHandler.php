<?php

declare(strict_types=1);

namespace App\Modules\Identity\Command\UpdateStatus;

use App\Modules\Identity\Entity\User\UserRepository;
use ZayMedia\Shared\Components\Flusher;

final readonly class IdentityUpdateStatusHandler
{
    public function __construct(
        private UserRepository $userRepository,
        private Flusher $flusher,
    ) {}

    public function handle(IdentityUpdateStatusCommand $command): void
    {
        $user = $this->userRepository->getById($command->userId);

        $user->setStatus($command->status);

        $this->flusher->flush();
    }
}
