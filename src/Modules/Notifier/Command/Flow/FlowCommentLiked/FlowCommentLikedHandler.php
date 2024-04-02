<?php

declare(strict_types=1);

namespace App\Modules\Notifier\Command\Flow\FlowCommentLiked;

use App\Modules\Flow\Entity\FlowComment\FlowCommentRepository;
use App\Modules\Identity\Entity\User\UserRepository;
use App\Modules\Notifier\Helpers\NotifierCategory;
use App\Modules\Notifier\Helpers\NotifierHelper;
use App\Modules\Notifier\Helpers\NotifierSound;
use App\Modules\Notifier\Service\Pusher\Command;
use App\Modules\Notifier\Service\Pusher\Pusher;
use App\Modules\OAuth\Query\GetPushTokensByUserId\GetPushTokensByUserIdFetcher;

final readonly class FlowCommentLikedHandler
{
    public function __construct(
        private UserRepository $userRepository,
        private FlowCommentRepository $flowCommentRepository,
        private GetPushTokensByUserIdFetcher $pushTokensByUserIdFetcher,
        private Pusher $pusher,
        private NotifierHelper $notifierHelper,
    ) {}

    public function handle(FlowCommentLikedCommand $command): void
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

        $user       = $this->userRepository->getById($command->userId);
        $category   = NotifierCategory::FLOW_COMMENT_LIKED;
        $thread     = $this->notifierHelper->getThreadName($category, $flowComment->getFlowId());
        $text       = $this->notifierHelper->getCommentText($flowComment);

        $data = [
            'link'          => $this->notifierHelper->getFlowLink($flowComment->getFlowId()),
            'iconUrl'       => null,
            'thumbnailUrl'  => null,
            'attachmentUrl' => $this->notifierHelper->getCommentAttachmentUrl($flowComment),
            'id'            => $this->notifierHelper->getId($category, $command->likeId),
        ];

        foreach ($tokens as $info) {
            $this->pusher->send(
                new Command(
                    bundleId: $info['bundleId'],
                    platform: $info['platform'],
                    locale: $info['locale'],
                    tokens: $info['tokens'],
                    title: 'KEY_31',
                    body: 'KEY_33',
                    subtitle: 'KEY_32',
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
