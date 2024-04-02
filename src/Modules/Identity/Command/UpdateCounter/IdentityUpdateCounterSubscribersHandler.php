<?php

declare(strict_types=1);

namespace App\Modules\Identity\Command\UpdateCounter;

use App\Modules\Contact\Entity\ContactRequest\ContactRequestRepository;
use App\Modules\Identity\Entity\User\UserRepository;
use ZayMedia\Shared\Components\Flusher;

final readonly class IdentityUpdateCounterSubscribersHandler
{
    public function __construct(
        private UserRepository $userRepository,
        private ContactRequestRepository $contactRequestRepository,
        private Flusher $flusher
    ) {}

    public function handle(int $id): void
    {
        $user = $this->userRepository->getById($id);

        $user->setCountSubscribers(
            $this->contactRequestRepository->countByContact($user)
        );

        $this->flusher->flush();
    }
}
