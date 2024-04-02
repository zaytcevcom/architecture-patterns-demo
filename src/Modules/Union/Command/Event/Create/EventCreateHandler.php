<?php

declare(strict_types=1);

namespace App\Modules\Union\Command\Event\Create;

use App\Modules\Identity\Entity\User\UserRepository;
use App\Modules\Union\Command\CreateSystemData\UnionCreateSystemDataCommand;
use App\Modules\Union\Command\CreateSystemData\UnionCreateSystemDataHandler;
use App\Modules\Union\Command\UpdateCounter\UnionUpdateCounterMembersHandler;
use App\Modules\Union\Entity\Union\Union;
use App\Modules\Union\Entity\Union\UnionRepository;
use App\Modules\Union\Entity\UnionEventInfo\UnionEventInfo;
use App\Modules\Union\Entity\UnionEventInfo\UnionEventInfoRepository;
use App\Modules\Union\Entity\UnionUser\UnionUser;
use App\Modules\Union\Entity\UnionUser\UnionUserRepository;
use App\Modules\Union\Event\Event\EventEventPublisher;
use App\Modules\Union\Event\Event\EventQueue;
use ZayMedia\Shared\Components\Flusher;

final readonly class EventCreateHandler
{
    public function __construct(
        private UserRepository $userRepository,
        private UnionRepository $unionRepository,
        private UnionEventInfoRepository $unionEventInfoRepository,
        private UnionUserRepository $unionUserRepository,
        private UnionUpdateCounterMembersHandler $unionUpdateCounterMembersHandler,
        private UnionCreateSystemDataHandler $unionCreateSystemDataHandler,
        private Flusher $flusher,
        private EventEventPublisher $eventPublisher,
    ) {}

    public function handle(EventCreateCommand $command): int
    {
        $user = $this->userRepository->getById($command->creatorId);

        // todo:
        // Check max limit
        // Check daily limit

        $union = Union::createEvent(
            name: $command->name,
            creatorId: $user->getId(),
            categoryId: $command->categoryId,
            description: $command->description,
        );

        $this->unionRepository->add($union);

        $this->flusher->flush();

        $this->addInfo($union->getId(), $command);

        $this->addMembers($user->getId(), $union->getId());

        $this->unionCreateSystemDataHandler->handle(
            new UnionCreateSystemDataCommand(
                userId: $user->getId(),
                unionId: $union->getId(),
                photoHost: $command->photoHost,
                photoFileId: $command->photoFileId
            )
        );

        $this->unionUpdateCounterMembersHandler->handle($union->getId());

        $this->eventPublisher->handle(EventQueue::CREATED, $union->getId());

        return $union->getId();
    }

    private function addInfo(int $unionId, EventCreateCommand $command): void
    {
        /** @var array{timeStart: int, timeEnd: int} $date */
        foreach ($command->dates as $date) {
            $unionEventInfo = UnionEventInfo::create(
                unionId: $unionId,
                placeId: $command->placeId,
                timeStart: $date['timeStart'],
                timeEnd: strtotime(date('Y-m-d 23:59:59', $date['timeStart'])),
            );

            $this->unionEventInfoRepository->add($unionEventInfo);
        }

        $this->flusher->flush();
    }

    private function addMembers(int $userId, int $unionId): void
    {
        $unionUser = UnionUser::joinCreator(
            userId: $userId,
            unionId: $unionId
        );

        $this->unionUserRepository->add($unionUser);

        $this->flusher->flush();
    }
}
