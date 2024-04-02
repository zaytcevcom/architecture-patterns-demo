<?php

declare(strict_types=1);

namespace App\Modules\Identity\Command\UpdateCounter;

use App\Modules\Contact\Entity\Contact\ContactRepository;
use App\Modules\Identity\Entity\User\UserRepository;
use ZayMedia\Shared\Components\Flusher;

final readonly class IdentityUpdateCounterContactsHandler
{
    public function __construct(
        private UserRepository $userRepository,
        private ContactRepository $contactRepository,
        private Flusher $flusher
    ) {}

    public function handle(int $id): void
    {
        $user = $this->userRepository->getById($id);

        $user->setCountContacts(
            $this->contactRepository->countByUser($user)
        );

        $this->flusher->flush();
    }
}
