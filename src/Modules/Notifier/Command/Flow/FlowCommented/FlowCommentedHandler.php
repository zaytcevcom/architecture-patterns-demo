<?php

declare(strict_types=1);

namespace App\Modules\Notifier\Command\Flow\FlowCommented;

use App\Modules\Flow\Entity\Flow\FlowRepository;
use App\Modules\Identity\Entity\User\User;
use App\Modules\Identity\Entity\User\UserRepository;
use App\Modules\Notifier\Helpers\NotifierCategory;
use App\Modules\Notifier\Helpers\NotifierHelper;
use App\Modules\Notifier\Helpers\NotifierSound;
use App\Modules\Notifier\Service\Pusher\Command;
use App\Modules\Notifier\Service\Pusher\Pusher;
use App\Modules\OAuth\Query\GetPushTokensByUserId\GetPushTokensByUserIdFetcher;

final readonly class FlowCommentedHandler
{
    public function __construct(
        private UserRepository $userRepository,
        private FlowRepository $flowRepository,
        private GetPushTokensByUserIdFetcher $pushTokensByUserIdFetcher,
        private Pusher $pusher,
        private NotifierHelper $notifierHelper,
    ) {}

    public function handle(FlowCommentedCommand $command): void
    {
        $flow = $this->flowRepository->getById($command->flowId);

        if ($flow->getUnionId() !== null || $flow->getUserId() === $command->userId) {
            return;
        }

        $tokens = $this->pusher->getGroupedTokens(
            $this->pushTokensByUserIdFetcher->fetch($flow->getUserId())
        );

        if (empty($tokens)) {
            return;
        }

        $user = $this->userRepository->getById($command->userId);
        $text = $flow->getDescription() ?? '🖼 Поток'; // todo

        $category   = NotifierCategory::FLOW_COMMENTED;
        $thread     = $this->notifierHelper->getThreadName($category, $flow->getId());

        $data = [
            'link'          => $this->notifierHelper->getFlowLink($flow->getId()),
            'iconUrl'       => null,
            'thumbnailUrl'  => null,
            'attachmentUrl' => User::getPhotoParsed($user->getPhoto()?->getValue())['xs'] ?? null,
            'locale'        => [
                'like'                      => 'BUTTON_2',
                'textInputTitle'            => 'BUTTON_1',
                'textInputButtonTitle'      => 'BUTTON_7',
                'textInputPlaceholder'      => 'PLACEHOLDER_1',
            ],
            'id'        => $this->notifierHelper->getId($category, $user->getId()),
            'flowId'    => $flow->getId(),
            'commentId' => 0, // todo
        ];

        foreach ($tokens as $info) {
            $this->pusher->send(
                new Command(
                    bundleId: $info['bundleId'],
                    platform: $info['platform'],
                    locale: $info['locale'],
                    tokens: $info['tokens'],
                    title: 'KEY_7',
                    body: 'KEY_9',
                    subtitle: 'KEY_8',
                    category: $category->value,
                    thread: $thread,
                    data: $data,
                    badge: null,
                    sound: NotifierSound::DEFAULT->value,
                    translateParams: [
                        '%firstName'    => $this->notifierHelper->getFirstName($user, $info['locale']),
                        '%lastName'     => $this->notifierHelper->getLastName($user, $info['locale']),
                        '%CommentText'  => $this->pusher->translate($text, [], $info['locale']),
                    ]
                )
            );
        }
    }
}
