<?php

declare(strict_types=1);

namespace App\Modules\Union\Command\Place\Create;

use App\Modules\Identity\Entity\User\UserRepository;
use App\Modules\Union\Command\CreateSystemData\UnionCreateSystemDataCommand;
use App\Modules\Union\Command\CreateSystemData\UnionCreateSystemDataHandler;
use App\Modules\Union\Command\UpdateCounter\UnionUpdateCounterMembersHandler;
use App\Modules\Union\Entity\Union\Union;
use App\Modules\Union\Entity\Union\UnionRepository;
use App\Modules\Union\Entity\UnionPlaceInfo\UnionPlaceInfo;
use App\Modules\Union\Entity\UnionPlaceInfo\UnionPlaceInfoRepository;
use App\Modules\Union\Entity\UnionUser\UnionUser;
use App\Modules\Union\Entity\UnionUser\UnionUserRepository;
use App\Modules\Union\Event\Place\PlaceEventPublisher;
use App\Modules\Union\Event\Place\PlaceQueue;
use ZayMedia\Shared\Components\Flusher;

final readonly class PlaceCreateHandler
{
    public function __construct(
        private UserRepository $userRepository,
        private UnionRepository $unionRepository,
        private UnionPlaceInfoRepository $unionPlaceInfoRepository,
        private UnionUserRepository $unionUserRepository,
        private UnionUpdateCounterMembersHandler $unionUpdateCounterMembersHandler,
        private UnionCreateSystemDataHandler $unionCreateSystemDataHandler,
        private Flusher $flusher,
        private PlaceEventPublisher $eventPublisher,
    ) {}

    public function handle(PlaceCreateCommand $command): int
    {
        $user = $this->userRepository->getById($command->creatorId);

        // todo:
        // Check max limit
        // Check daily limit

        $union = Union::createPlace(
            name: $command->name,
            creatorId: $user->getId(),
            categoryId: $command->categoryId,
            description: $command->description,
            website: $command->website
        );

        $union->setCityId($command->cityId);

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

        $this->eventPublisher->handle(PlaceQueue::CREATED, $union->getId());

        return $union->getId();
    }

    private function addInfo(int $unionId, PlaceCreateCommand $command): void
    {
        $unionPlaceInfo = UnionPlaceInfo::create(
            unionId: $unionId,
            location: $command->address,
            latitude: $command->latitude,
            longitude: $command->longitude,
            workingHours: $command->workingHours,
            email: null,
            phone: null,
            phoneDescription: null
        );

        $this->unionPlaceInfoRepository->add($unionPlaceInfo);

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
