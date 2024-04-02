<?php

declare(strict_types=1);

namespace App\Modules\Notifier\Command\Flow\FlowCommentAnswered;

use App\Modules\Flow\Entity\FlowComment\FlowCommentRepository;
use App\Modules\Identity\Entity\User\User;
use App\Modules\Identity\Entity\User\UserRepository;
use App\Modules\Notifier\Helpers\NotifierCategory;
use App\Modules\Notifier\Helpers\NotifierHelper;
use App\Modules\Notifier\Helpers\NotifierSound;
use App\Modules\Notifier\Service\Pusher\Command;
use App\Modules\Notifier\Service\Pusher\Pusher;
use App\Modules\OAuth\Query\GetPushTokensByUserId\GetPushTokensByUserIdFetcher;

final readonly class FlowCommentAnsweredHandler
{
    public function __construct(
        private UserRepository $userRepository,
        private FlowCommentRepository $flowCommentRepository,
        private GetPushTokensByUserIdFetcher $pushTokensByUserIdFetcher,
        private Pusher $pusher,
        private NotifierHelper $notifierHelper,
    ) {}

    public function handle(FlowCommentAnsweredCommand $command): void
    {
        $flowComment = $this->flowCommentRepository->getById($command->commentId);

        if ($flowComment->getUnionId() !== null || $flowComment->getUserId() === $command->userId) {
            return;
        }

        $tokens = $this->pusher->getGroupedTokens(
            $this->pushTokensByUserIdFetcher->fetch($flowComment->getUserId())
        );

        if (empty($tokens)) {
            return;
        }

        $user = $this->userRepository->getById($command->userId);
        $text = $flowComment->getMessage() ?? '🖼 Фотография'; // todo

        $category   = NotifierCategory::FLOW_COMMENT_ANSWERED;
        $thread     = $this->notifierHelper->getThreadName($category, $flowComment->getFlowId());

        $data = [
            'link'          => $this->notifierHelper->getFlowLink($flowComment->getFlowId()),
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
            'flowId'    => $flowComment->getFlowId(),
            'commentId' => $flowComment->getId(),
        ];

        foreach ($tokens as $info) {
            $this->pusher->send(
                new Command(
                    bundleId: $info['bundleId'],
                    platform: $info['platform'],
                    locale: $info['locale'],
                    tokens: $info['tokens'],
                    title: 'KEY_10',
                    body: 'KEY_12',
                    subtitle: 'KEY_11',
                    category: $category->value,
                    thread: $thread,
                    data: $data,
                    badge: null,
                    sound: NotifierSound::DEFAULT->value,
                    translateParams: [
                        '%firstName'    => $this->notifierHelper->getFirstName($user, $info['locale']),
                        '%lastName'     => $this->notifierHelper->getLastName($user, $info['locale']),
                        '%ReplyText'    => $this->pusher->translate($text, [], $info['locale']),
                    ]
                )
            );
        }
    }
}
