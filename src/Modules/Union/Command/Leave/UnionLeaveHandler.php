<?php

declare(strict_types=1);

namespace App\Modules\Union\Command\Leave;

use App\Modules\Identity\Command\UpdateCounter\IdentityUpdateCounterCommunitiesHandler;
use App\Modules\Identity\Command\UpdateCounter\IdentityUpdateCounterEventsHandler;
use App\Modules\Identity\Command\UpdateCounter\IdentityUpdateCounterPlacesHandler;
use App\Modules\Identity\Entity\User\UserRepository;
use App\Modules\Union\Command\UpdateCounter\UnionUpdateCounterMembersHandler;
use App\Modules\Union\Entity\Union\Union;
use App\Modules\Union\Entity\Union\UnionRepository;
use App\Modules\Union\Entity\UnionUser\UnionUserRepository;
use ZayMedia\Shared\Components\Flusher;

final readonly class UnionLeaveHandler
{
    public function __construct(
        private UserRepository $userRepository,
        private UnionRepository $unionRepository,
        private UnionUserRepository $unionUserRepository,
        private UnionUpdateCounterMembersHandler $unionUpdateCounterMembersHandler,
        private IdentityUpdateCounterCommunitiesHandler $identityUpdateCounterCommunitiesHandler,
        private IdentityUpdateCounterEventsHandler $identityUpdateCounterEventsHandler,
        private IdentityUpdateCounterPlacesHandler $identityUpdateCounterPlacesHandler,
        private Flusher $flusher
    ) {}

    public function handle(UnionLeaveCommand $command): void
    {
        $user   = $this->userRepository->getById($command->userId);
        $union  = $this->unionRepository->getById($command->unionId);

        $unionUser = $this->unionUserRepository->findByUserAndUnionIds(
            userId: $user->getId(),
            unionId: $union->getId()
        );

        if (!empty($unionUser)) {
            $this->unionUserRepository->remove($unionUser);
            $this->flusher->flush();

            $this->unionUpdateCounterMembersHandler->handle($union->getId());

            $this->updateUserCounters($union->getType(), $user->getId());
        }
    }

    private function updateUserCounters(int $type, int $userId): void
    {
        match ($type) {
            Union::typeEvent() => $this->identityUpdateCounterEventsHandler->handle($userId),
            Union::typePlace() => $this->identityUpdateCounterPlacesHandler->handle($userId),
            default            => $this->identityUpdateCounterCommunitiesHandler->handle($userId),
        };
    }
}
