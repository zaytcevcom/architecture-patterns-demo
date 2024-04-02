<?php

declare(strict_types=1);

namespace App\Modules\Union\Command\Join;

use App\Modules\Identity\Command\UpdateCounter\IdentityUpdateCounterCommunitiesHandler;
use App\Modules\Identity\Command\UpdateCounter\IdentityUpdateCounterEventsHandler;
use App\Modules\Identity\Command\UpdateCounter\IdentityUpdateCounterPlacesHandler;
use App\Modules\Identity\Entity\User\UserRepository;
use App\Modules\Union\Command\UpdateCounter\UnionUpdateCounterMembersHandler;
use App\Modules\Union\Entity\Union\Union;
use App\Modules\Union\Entity\Union\UnionRepository;
use App\Modules\Union\Entity\UnionUser\UnionUser;
use App\Modules\Union\Entity\UnionUser\UnionUserRepository;
use ZayMedia\Shared\Components\Flusher;
use ZayMedia\Shared\Http\Exception\AccessDeniedException;
use ZayMedia\Shared\Http\Exception\DomainExceptionModule;

final readonly class UnionJoinHandler
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

    public function handle(UnionJoinCommand $command): void
    {
        $user   = $this->userRepository->getById($command->userId);
        $union  = $this->unionRepository->getById($command->unionId);

        if ($union->getKind() === Union::kindPrivate()) {
            throw new AccessDeniedException();
        }

        // Check max limit
        if (!$user->isBot() && $this->unionUserRepository->countByUserId($user->getId()) >= UnionUser::limitTotal()) {
            throw new DomainExceptionModule(
                module: 'union',
                message: 'error.union_user.limit_total',
                code: 3
            );
        }

        // Check daily limit
        if (!$user->isBot() && $this->unionUserRepository->countTodayByUserId($user->getId()) >= UnionUser::limitDaily()) {
            throw new DomainExceptionModule(
                module: 'union',
                message: 'error.union_user.limit_daily',
                code: 4
            );
        }

        $unionUser = $this->unionUserRepository->findByUserAndUnionIds(
            userId: $user->getId(),
            unionId: $union->getId()
        );

        if (!empty($unionUser)) {
            if ($unionUser->getRole() === Union::roleInvite()) {
                $unionUser->acceptInvite();

                $this->unionUserRepository->add($unionUser);

                $this->flusher->flush();

                $this->unionUpdateCounterMembersHandler->handle($union->getId());

                $this->updateUserCounters($union->getType(), $user->getId());
            }

            return;
        }

        if ($union->getKind() === Union::kindPublic()) {
            $unionUser = UnionUser::join(
                userId: $user->getId(),
                unionId: $union->getId()
            );
        } elseif ($union->getKind() === Union::kindClosed()) {
            $unionUser = UnionUser::sendRequest(
                userId: $user->getId(),
                unionId: $union->getId()
            );
        } else {
            return;
        }

        if ($union->getCreatorId() === $user->getId()) {
            $unionUser->setRole(Union::roleCreator());
        }

        $this->unionUserRepository->add($unionUser);

        $this->flusher->flush();

        $this->unionUpdateCounterMembersHandler->handle($union->getId());

        $this->updateUserCounters($union->getType(), $user->getId());
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
