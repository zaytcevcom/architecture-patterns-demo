<?php

declare(strict_types=1);

namespace App\Modules\Identity\Command\UpdateCounter;

use App\Modules\Identity\Entity\User\UserRepository;
use App\Modules\Union\Entity\UnionUser\UnionUserRepository;
use ZayMedia\Shared\Components\Flusher;

final readonly class IdentityUpdateCounterPlacesHandler
{
    public function __construct(
        private UserRepository $userRepository,
        private UnionUserRepository $unionUserRepository,
        private Flusher $flusher
    ) {}

    public function handle(int $id): void
    {
        $user = $this->userRepository->getById($id);

        $user->setCountPlaces(
            $this->unionUserRepository->countPlacesByUserId($user->getId())
        );

        $this->flusher->flush();
    }
}
