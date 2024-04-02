<?php

declare(strict_types=1);

namespace App\Modules\Identity\Command\UpdateCounter;

use App\Modules\Identity\Entity\User\UserRepository;
use App\Modules\Union\Entity\UnionUser\UnionUserRepository;
use ZayMedia\Shared\Components\Flusher;

final readonly class IdentityUpdateCounterCommunitiesHandler
{
    public function __construct(
        private UserRepository $userRepository,
        private UnionUserRepository $unionUserRepository,
        private Flusher $flusher
    ) {}

    public function handle(int $id): void
    {
        $user = $this->userRepository->getById($id);

        $user->setCountCommunities(
            $this->unionUserRepository->countCommunitiesByUserId($user->getId())
        );

        $this->flusher->flush();
    }
}
