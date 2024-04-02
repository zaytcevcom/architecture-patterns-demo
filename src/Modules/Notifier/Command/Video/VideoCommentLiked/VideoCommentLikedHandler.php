<?php

declare(strict_types=1);

namespace App\Modules\Notifier\Command\Video\VideoCommentLiked;

use App\Modules\Identity\Entity\User\UserRepository;
use App\Modules\Media\Entity\VideoComment\VideoCommentRepository;
use App\Modules\Notifier\Helpers\NotifierCategory;
use App\Modules\Notifier\Helpers\NotifierHelper;
use App\Modules\Notifier\Helpers\NotifierSound;
use App\Modules\Notifier\Service\Pusher\Command;
use App\Modules\Notifier\Service\Pusher\Pusher;
use App\Modules\OAuth\Query\GetPushTokensByUserId\GetPushTokensByUserIdFetcher;

final readonly class VideoCommentLikedHandler
{
    public function __construct(
        private UserRepository $userRepository,
        private VideoCommentRepository $videoCommentRepository,
        private GetPushTokensByUserIdFetcher $pushTokensByUserIdFetcher,
        private Pusher $pusher,
        private NotifierHelper $notifierHelper,
    ) {}

    public function handle(VideoCommentLikedCommand $command): void
    {
        $videoComment = $this->videoCommentRepository->getById($command->commentId);

        if ($videoComment->getUnionId() !== null || $videoComment->getUserId() === $command->userId) {
            return;
        }

        $tokens = $this->pusher->getGroupedTokens(
            $this->pushTokensByUserIdFetcher->fetch($videoComment->getUserId())
        );

        if (empty($tokens)) {
            return;
        }

        $user       = $this->userRepository->getById($command->userId);
        $category   = NotifierCategory::VIDEO_COMMENT_LIKED;
        $thread     = $this->notifierHelper->getThreadName($category, $videoComment->getVideoId());
        $text       = $this->notifierHelper->getCommentText($videoComment);

        $data = [
            'link'          => $this->notifierHelper->getVideoLink($videoComment->getVideoId()),
            'iconUrl'       => null,
            'thumbnailUrl'  => null,
            'attachmentUrl' => $this->notifierHelper->getCommentAttachmentUrl($videoComment),
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
