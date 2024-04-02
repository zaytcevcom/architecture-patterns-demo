<?php

declare(strict_types=1);

namespace App\Modules\Notifier\Command\Post\PostCommentLiked;

use App\Modules\Identity\Entity\User\UserRepository;
use App\Modules\Notifier\Helpers\NotifierCategory;
use App\Modules\Notifier\Helpers\NotifierHelper;
use App\Modules\Notifier\Helpers\NotifierSound;
use App\Modules\Notifier\Service\Pusher\Command;
use App\Modules\Notifier\Service\Pusher\Pusher;
use App\Modules\OAuth\Query\GetPushTokensByUserId\GetPushTokensByUserIdFetcher;
use App\Modules\Post\Entity\PostComment\PostCommentRepository;

final readonly class PostCommentLikedHandler
{
    public function __construct(
        private UserRepository $userRepository,
        private PostCommentRepository $postCommentRepository,
        private GetPushTokensByUserIdFetcher $pushTokensByUserIdFetcher,
        private Pusher $pusher,
        private NotifierHelper $notifierHelper,
    ) {}

    public function handle(PostCommentLikedCommand $command): void
    {
        $postComment = $this->postCommentRepository->getById($command->commentId);

        if ($postComment->getUnionId() !== null || $postComment->getUserId() === $command->userId) {
            return;
        }

        $tokens = $this->pusher->getGroupedTokens(
            $this->pushTokensByUserIdFetcher->fetch($postComment->getUserId())
        );

        if (empty($tokens)) {
            return;
        }

        $user       = $this->userRepository->getById($command->userId);
        $category   = NotifierCategory::POST_COMMENT_LIKED;
        $thread     = $this->notifierHelper->getThreadName($category, $postComment->getPostId());
        $text       = $this->notifierHelper->getCommentText($postComment);

        $data = [
            'link'          => $this->notifierHelper->getPostLink($postComment->getPostId()),
            'iconUrl'       => null,
            'thumbnailUrl'  => null,
            'attachmentUrl' => $this->notifierHelper->getCommentAttachmentUrl($postComment),
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
