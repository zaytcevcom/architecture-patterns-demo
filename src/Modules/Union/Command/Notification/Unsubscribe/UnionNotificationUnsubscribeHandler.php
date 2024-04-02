<?php

declare(strict_types=1);

namespace App\Modules\Union\Command\Notification\Unsubscribe;

use App\Modules\Identity\Entity\User\UserRepository;
use App\Modules\Union\Entity\Union\UnionRepository;
use App\Modules\Union\Entity\UnionNotification\UnionNotificationRepository;
use ZayMedia\Shared\Components\Flusher;

final readonly class UnionNotificationUnsubscribeHandler
{
    public function __construct(
        private UserRepository $userRepository,
        private UnionRepository $unionRepository,
        private UnionNotificationRepository $unionNotificationRepository,
        private Flusher $flusher
    ) {}

    public function handle(UnionNotificationUnsubscribeCommand $command): void
    {
        $user = $this->userRepository->getById($command->userId);
        $union  = $this->unionRepository->getById($command->unionId);

        $unionNotification = $this->unionNotificationRepository->findByUserAndUnionIds(
            userId: $user->getId(),
            unionId: $union->getId()
        );

        if (empty($unionNotification)) {
            return;
        }

        $this->unionNotificationRepository->remove($unionNotification);

        $this->flusher->flush();
    }
}
