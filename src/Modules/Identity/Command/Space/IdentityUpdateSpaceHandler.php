<?php

declare(strict_types=1);

namespace App\Modules\Identity\Command\Space;

use App\Modules\Identity\Entity\User\UserRepository;
use ZayMedia\Shared\Components\Flusher;

final readonly class IdentityUpdateSpaceHandler
{
    public function __construct(
        private UserRepository $userRepository,
        private Flusher $flusher,
    ) {}

    public function handle(IdentityUpdateSpaceCommand $command): void
    {
        $user = $this->userRepository->getById($command->userId);

        $user->setSpaceId($command->spaceId);

        $this->flusher->flush();
    }
}
