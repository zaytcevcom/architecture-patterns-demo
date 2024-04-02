<?php

declare(strict_types=1);

namespace App\Modules\Identity\Command\Location;

use App\Modules\Identity\Entity\User\UserRepository;
use ZayMedia\Shared\Components\Flusher;

final readonly class IdentityUpdateLocationHandler
{
    public function __construct(
        private UserRepository $userRepository,
        private Flusher $flusher,
    ) {}

    public function handle(IdentityUpdateLocationCommand $command): void
    {
        $user = $this->userRepository->getById($command->userId);

        $user->setLatitude($command->latitude);
        $user->setLongitude($command->longitude);

        $this->flusher->flush();
    }
}
