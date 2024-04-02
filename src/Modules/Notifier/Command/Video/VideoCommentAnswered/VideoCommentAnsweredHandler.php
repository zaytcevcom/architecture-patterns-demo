<?php

declare(strict_types=1);

namespace App\Modules\Notifier\Command\Video\VideoCommentAnswered;

use App\Modules\Identity\Entity\User\User;
use App\Modules\Identity\Entity\User\UserRepository;
use App\Modules\Media\Entity\VideoComment\VideoCommentRepository;
use App\Modules\Notifier\Helpers\NotifierCategory;
use App\Modules\Notifier\Helpers\NotifierHelper;
use App\Modules\Notifier\Helpers\NotifierSound;
use App\Modules\Notifier\Service\Pusher\Command;
use App\Modules\Notifier\Service\Pusher\Pusher;
use App\Modules\OAuth\Query\GetPushTokensByUserId\GetPushTokensByUserIdFetcher;

final readonly class VideoCommentAnsweredHandler
{
    public function __construct(
        private UserRepository $userRepository,
        private VideoCommentRepository $videoCommentRepository,
        private GetPushTokensByUserIdFetcher $pushTokensByUserIdFetcher,
        private Pusher $pusher,
        private NotifierHelper $notifierHelper,
    ) {}

    public function handle(VideoCommentAnsweredCommand $command): void
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

        $user = $this->userRepository->getById($command->userId);
        $text = $videoComment->getMessage() ?? '🖼 Фотография'; // todo

        $category   = NotifierCategory::VIDEO_COMMENT_ANSWERED;
        $thread     = $this->notifierHelper->getThreadName($category, $videoComment->getVideoId());

        $data = [
            'link'          => $this->notifierHelper->getVideoLink($videoComment->getVideoId()),
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
            'videoId'   => $videoComment->getVideoId(),
            'commentId' => $videoComment->getId(),
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
