<?php

declare(strict_types=1);

namespace App\Modules\Union\Command\Community\Create;

use App\Modules\Identity\Entity\User\UserRepository;
use App\Modules\Union\Command\CreateSystemData\UnionCreateSystemDataCommand;
use App\Modules\Union\Command\CreateSystemData\UnionCreateSystemDataHandler;
use App\Modules\Union\Command\UpdateCounter\UnionUpdateCounterMembersHandler;
use App\Modules\Union\Entity\Union\Union;
use App\Modules\Union\Entity\Union\UnionRepository;
use App\Modules\Union\Entity\UnionUser\UnionUser;
use App\Modules\Union\Entity\UnionUser\UnionUserRepository;
use App\Modules\Union\Event\Community\CommunityEventPublisher;
use App\Modules\Union\Event\Community\CommunityQueue;
use ZayMedia\Shared\Components\Flusher;

final readonly class CommunityCreateHandler
{
    public function __construct(
        private UserRepository $userRepository,
        private UnionRepository $unionRepository,
        private UnionUserRepository $unionUserRepository,
        private UnionUpdateCounterMembersHandler $unionUpdateCounterMembersHandler,
        private UnionCreateSystemDataHandler $unionCreateSystemDataHandler,
        private Flusher $flusher,
        private CommunityEventPublisher $eventPublisher,
    ) {}

    public function handle(CommunityCreateCommand $command): int
    {
        $user = $this->userRepository->getById($command->creatorId);

        // todo:
        // Check max limit
        // Check daily limit

        $union = Union::createCommunity(
            name: $command->name,
            creatorId: $user->getId(),
            categoryId: $command->categoryId,
            description: $command->description,
            website: $command->website
        );
        $union->setCityId($command->cityId);

        $this->unionRepository->add($union);

        $this->flusher->flush();

        $unionUser = UnionUser::joinCreator(
            userId: $user->getId(),
            unionId: $union->getId()
        );

        $this->unionUserRepository->add($unionUser);

        $this->flusher->flush();

        $this->unionCreateSystemDataHandler->handle(
            new UnionCreateSystemDataCommand(
                userId: $user->getId(),
                unionId: $union->getId(),
                photoHost: $command->photoHost,
                photoFileId: $command->photoFileId
            )
        );

        $this->unionUpdateCounterMembersHandler->handle($union->getId());

        $this->eventPublisher->handle(CommunityQueue::CREATED, $union->getId());

        return $union->getId();
    }
}
