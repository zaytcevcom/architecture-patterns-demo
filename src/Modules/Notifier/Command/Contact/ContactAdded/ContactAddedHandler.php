<?php

declare(strict_types=1);

namespace App\Modules\Notifier\Command\Contact\ContactAdded;

use App\Modules\Identity\Entity\User\UserRepository;
use App\Modules\Notifier\Command\Badge\BadgeCommand;
use App\Modules\Notifier\Command\Badge\BadgeHandler;
use App\Modules\Notifier\Helpers\NotifierCategory;
use App\Modules\Notifier\Helpers\NotifierHelper;
use App\Modules\Notifier\Helpers\NotifierSound;
use App\Modules\Notifier\Service\Pusher\Command;
use App\Modules\Notifier\Service\Pusher\Pusher;
use App\Modules\OAuth\Query\GetPushTokensByUserId\GetPushTokensByUserIdFetcher;

final readonly class ContactAddedHandler
{
    public function __construct(
        private UserRepository $userRepository,
        private GetPushTokensByUserIdFetcher $pushTokensByUserIdFetcher,
        private BadgeHandler $badgeHandler,
        private Pusher $pusher,
        private NotifierHelper $notifierHelper
    ) {}

    public function handle(ContactAddedCommand $command): void
    {
        if ($command->targetId === $command->userId) {
            return;
        }

        $target = $this->userRepository->getById($command->targetId);

        $tokens = $this->pusher->getGroupedTokens(
            $this->pushTokensByUserIdFetcher->fetch($target->getId())
        );

        if (empty($tokens)) {
            return;
        }

        $user       = $this->userRepository->getById($command->userId);
        $category   = NotifierCategory::CONTACT_ADDED;
        $thread     = $this->notifierHelper->getThreadName($category, $user->getId());
        $body       = $user->isFemale() ? 'KEY_6' : 'KEY_5';

        $data = [
            'link'          => $this->notifierHelper->getUserLink($user->getId()),
            'iconUrl'       => null,
            'thumbnailUrl'  => null,
            'attachmentUrl' => $this->notifierHelper->getUserPhoto($user),
            'locale'        => [
                'textInputTitle'            => 'title',
                'textInputButtonTitle'      => 'button',
                'textInputPlaceholder'      => 'placeholder',
            ],
            'userId' => $user->getId(),
            'id'     => $this->notifierHelper->getId($category, $user->getId()),
        ];

        foreach ($tokens as $info) {
            $this->pusher->send(
                new Command(
                    bundleId: $info['bundleId'],
                    platform: $info['platform'],
                    locale: $info['locale'],
                    tokens: $info['tokens'],
                    title: 'KEY_4',
                    body: $body,
                    category: $category->value,
                    thread: $thread,
                    data: $data,
                    badge: null,
                    sound: NotifierSound::DEFAULT->value,
                    translateParams: [
                        '%firstName'    => $this->notifierHelper->getFirstName($user, $info['locale']),
                        '%lastName'     => $this->notifierHelper->getLastName($user, $info['locale']),
                    ]
                )
            );
        }

        $this->badgeHandler->handle(
            new BadgeCommand(
                userId: $user->getId()
            )
        );
    }
}
