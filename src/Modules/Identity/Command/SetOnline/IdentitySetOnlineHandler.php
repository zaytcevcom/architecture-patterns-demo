<?php

declare(strict_types=1);

namespace App\Modules\Identity\Command\SetOnline;

use App\Modules\Identity\Entity\User\UserRepository;
use ZayMedia\Shared\Components\Flusher;

final readonly class IdentitySetOnlineHandler
{
    public function __construct(
        private UserRepository $userRepository,
        private Flusher $flusher,
    ) {}

    public function handle(int $userId): void
    {
        $user = $this->userRepository->getById($userId);

        $user->setOnline();

        $this->flusher->flush();
    }
}
