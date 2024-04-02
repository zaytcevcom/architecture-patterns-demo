<?php

declare(strict_types=1);

namespace App\Modules\Identity\Command\Delete;

use App\Modules\Identity\Entity\User\UserRepository;
use ZayMedia\Shared\Components\Flusher;

final readonly class IdentityDeleteHandler
{
    public function __construct(
        private UserRepository $userRepository,
        private Flusher $flusher,
    ) {}

    public function handle(IdentityDeleteCommand $command): void
    {
        $user = $this->userRepository->getById($command->userId);

        $user->setDeleted();

        $this->userRepository->add($user);

        $this->flusher->flush();
    }
}
