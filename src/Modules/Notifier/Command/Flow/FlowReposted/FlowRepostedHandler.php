<?php

declare(strict_types=1);

namespace App\Modules\Notifier\Command\Flow\FlowReposted;

use App\Modules\Flow\Entity\Flow\FlowRepository;
use App\Modules\Identity\Entity\User\UserRepository;
use App\Modules\Notifier\Helpers\NotifierCategory;
use App\Modules\Notifier\Helpers\NotifierHelper;
use App\Modules\Notifier\Helpers\NotifierSound;
use App\Modules\Notifier\Service\Pusher\Command;
use App\Modules\Notifier\Service\Pusher\Pusher;
use App\Modules\OAuth\Query\GetPushTokensByUserId\GetPushTokensByUserIdFetcher;

final readonly class FlowRepostedHandler
{
    public function __construct(
        private UserRepository $userRepository,
        private FlowRepository $flowRepository,
        private GetPushTokensByUserIdFetcher $pushTokensByUserIdFetcher,
        private Pusher $pusher,
        private NotifierHelper $notifierHelper,
    ) {}

    public function handle(FlowRepostedCommand $command): void
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

        $user       = $this->userRepository->getById($command->userId);
        $category   = NotifierCategory::POST_REPOSTED;
        $thread     = $this->notifierHelper->getThreadName($category, $flow->getId());
        $body       = $user->isFemale() ? 'KEY_21' : 'KEY_20';
        $text       = $this->notifierHelper->getFlowText($flow);

        $data = [
            'link'          => $this->notifierHelper->getFlowLink($flow->getId()),
            'iconUrl'       => null,
            'thumbnailUrl'  => null,
            'attachmentUrl' => $this->notifierHelper->getFlowAttachmentUrl($flow),
            'locale'        => [
                'like' => 'BUTTON_2',
            ],
            'id'     => $this->notifierHelper->getId($category, $flow->getId()),
            'flowId' => $flow->getId(),
        ];

        foreach ($tokens as $info) {
            $this->pusher->send(
                new Command(
                    bundleId: $info['bundleId'],
                    platform: $info['platform'],
                    locale: $info['locale'],
                    tokens: $info['tokens'],
                    title: 'KEY_19',
                    body: $body,
                    category: $category->value,
                    thread: $thread,
                    data: $data,
                    badge: null,
                    sound: NotifierSound::NOTIFICATION->value,
                    translateParams: [
                        '%firstName'        => $this->notifierHelper->getFirstName($user, $info['locale']),
                        '%lastName'         => $this->notifierHelper->getLastName($user, $info['locale']),
                        '%RecordingText'    => $this->pusher->translate($text, [], $info['locale']),
                    ]
                )
            );
        }
    }
}
