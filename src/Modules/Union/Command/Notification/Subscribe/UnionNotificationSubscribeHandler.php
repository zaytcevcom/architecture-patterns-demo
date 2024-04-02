<?php

declare(strict_types=1);

namespace App\Modules\Union\Command\Notification\Subscribe;

use App\Modules\Identity\Entity\User\UserRepository;
use App\Modules\Union\Entity\Union\UnionRepository;
use App\Modules\Union\Entity\UnionNotification\UnionNotification;
use App\Modules\Union\Entity\UnionNotification\UnionNotificationRepository;
use ZayMedia\Shared\Components\Flusher;
use ZayMedia\Shared\Http\Exception\DomainExceptionModule;

final readonly class UnionNotificationSubscribeHandler
{
    public function __construct(
        private UserRepository $userRepository,
        private UnionRepository $unionRepository,
        private UnionNotificationRepository $unionNotificationRepository,
        private Flusher $flusher
    ) {}

    public function handle(UnionNotificationSubscribeCommand $command): void
    {
        $user   = $this->userRepository->getById($command->userId);
        $union  = $this->unionRepository->getById($command->unionId);

        // Check max limit
        if ($this->unionNotificationRepository->countByUserId($user->getId()) >= UnionNotification::limitTotal()) {
            throw new DomainExceptionModule(
                module: 'union',
                message: 'error.union_notification.limit_total',
                code: 3
            );
        }

        // Check daily limit
        if ($this->unionNotificationRepository->countTodayByUserId($user->getId()) >= UnionNotification::limitDaily()) {
            throw new DomainExceptionModule(
                module: 'union',
                message: 'error.union_notification.limit_daily',
                code: 4
            );
        }

        $unionNotification = $this->unionNotificationRepository->findByUserAndUnionIds(
            userId: $user->getId(),
            unionId: $union->getId()
        );

        if (!empty($unionNotification)) {
            return;
        }

        $unionNotification = UnionNotification::create(
            userId: $user->getId(),
            unionId: $union->getId()
        );

        $this->unionNotificationRepository->add($unionNotification);

        $this->flusher->flush();
    }
}
