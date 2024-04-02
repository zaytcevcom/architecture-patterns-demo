<?php

declare(strict_types=1);

namespace App\Modules\Notifier\Command\Post\PostReposted;

use App\Modules\Identity\Entity\User\UserRepository;
use App\Modules\Notifier\Helpers\NotifierCategory;
use App\Modules\Notifier\Helpers\NotifierHelper;
use App\Modules\Notifier\Helpers\NotifierSound;
use App\Modules\Notifier\Service\Pusher\Command;
use App\Modules\Notifier\Service\Pusher\Pusher;
use App\Modules\OAuth\Query\GetPushTokensByUserId\GetPushTokensByUserIdFetcher;
use App\Modules\Post\Entity\Post\PostRepository;

final readonly class PostRepostedHandler
{
    public function __construct(
        private UserRepository $userRepository,
        private PostRepository $postRepository,
        private GetPushTokensByUserIdFetcher $pushTokensByUserIdFetcher,
        private Pusher $pusher,
        private NotifierHelper $notifierHelper,
    ) {}

    public function handle(PostRepostedCommand $command): void
    {
        $post = $this->postRepository->getById($command->postId);

        if ($post->getUnionId() !== null || $post->getUserId() === $command->userId) {
            return;
        }

        $tokens = $this->pusher->getGroupedTokens(
            $this->pushTokensByUserIdFetcher->fetch($post->getUserId())
        );

        if (empty($tokens)) {
            return;
        }

        $user       = $this->userRepository->getById($command->userId);
        $category   = NotifierCategory::POST_REPOSTED;
        $thread     = $this->notifierHelper->getThreadName($category, $post->getId());
        $body       = $user->isFemale() ? 'KEY_21' : 'KEY_20';
        $text       = $this->notifierHelper->getPostText($post);

        $data = [
            'link'          => $this->notifierHelper->getPostLink($post->getId()),
            'iconUrl'       => null,
            'thumbnailUrl'  => null,
            'attachmentUrl' => $this->notifierHelper->getPostAttachmentUrl($post),
            'locale'        => [
                'like' => 'BUTTON_2',
            ],
            'id'     => $this->notifierHelper->getId($category, $post->getId()),
            'postId' => $post->getId(),
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
