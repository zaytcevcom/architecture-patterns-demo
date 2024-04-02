<?php

declare(strict_types=1);

namespace App\Modules\Notifier\Command\Photo\PhotoCommentLiked;

use App\Modules\Identity\Entity\User\UserRepository;
use App\Modules\Notifier\Helpers\NotifierCategory;
use App\Modules\Notifier\Helpers\NotifierHelper;
use App\Modules\Notifier\Helpers\NotifierSound;
use App\Modules\Notifier\Service\Pusher\Command;
use App\Modules\Notifier\Service\Pusher\Pusher;
use App\Modules\OAuth\Query\GetPushTokensByUserId\GetPushTokensByUserIdFetcher;
use App\Modules\Photo\Entity\PhotoComment\PhotoCommentRepository;

final readonly class PhotoCommentLikedHandler
{
    public function __construct(
        private UserRepository $userRepository,
        private PhotoCommentRepository $photoCommentRepository,
        private GetPushTokensByUserIdFetcher $pushTokensByUserIdFetcher,
        private Pusher $pusher,
        private NotifierHelper $notifierHelper,
    ) {}

    public function handle(PhotoCommentLikedCommand $command): void
    {
        $photoComment = $this->photoCommentRepository->getById($command->commentId);

        if ($photoComment->getUnionId() !== null || $photoComment->getUserId() === $command->userId) {
            return;
        }

        $tokens = $this->pusher->getGroupedTokens(
            $this->pushTokensByUserIdFetcher->fetch($photoComment->getUserId())
        );

        if (empty($tokens)) {
            return;
        }

        $user       = $this->userRepository->getById($command->userId);
        $category   = NotifierCategory::PHOTO_COMMENT_LIKED;
        $thread     = $this->notifierHelper->getThreadName($category, $photoComment->getPhotoId());
        $text       = $this->notifierHelper->getCommentText($photoComment);

        $data = [
            'link'          => $this->notifierHelper->getPhotoLink($photoComment->getPhotoId()),
            'iconUrl'       => null,
            'thumbnailUrl'  => null,
            'attachmentUrl' => $this->notifierHelper->getCommentAttachmentUrl($photoComment),
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
