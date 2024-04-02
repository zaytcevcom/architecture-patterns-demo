<?php

declare(strict_types=1);

namespace App\Modules\Notifier\Command\Post\PostPublished;

use App\Modules\Identity\Entity\User\User;
use App\Modules\Identity\Entity\User\UserRepository;
use App\Modules\Notifier\Helpers\NotifierCategory;
use App\Modules\Notifier\Helpers\NotifierHelper;
use App\Modules\Notifier\Helpers\NotifierSound;
use App\Modules\Notifier\Service\Pusher\Command;
use App\Modules\Notifier\Service\Pusher\Pusher;
use App\Modules\OAuth\Query\GetPushTokensByUserId\GetPushTokensByUserIdFetcher;
use App\Modules\Post\Entity\Post\PostRepository;
use App\Modules\Union\Entity\Union\UnionRepository;

final readonly class PostPublishedHandler
{
    public function __construct(
        private UserRepository $userRepository,
        private UnionRepository $unionRepository,
        private PostRepository $postRepository,
        private GetPushTokensByUserIdFetcher $pushTokensByUserIdFetcher,
        private Pusher $pusher,
        private NotifierHelper $notifierHelper,
    ) {}

    public function handle(PostPublishedCommand $command): void
    {
        $post = $this->postRepository->getById($command->postId);

        $tokens = $this->pusher->getGroupedTokens(
            $this->pushTokensByUserIdFetcher->fetch($command->userId)
        );

        if (empty($tokens)) {
            return;
        }

        $category   = NotifierCategory::POST_PUBLISHED;
        $thread     = $this->notifierHelper->getThreadName($category, $post->getUnionId() ?? $post->getUserId());

        $data = [
            'link'          => $this->notifierHelper->getPostLink($post->getId()),
            'iconUrl'       => null,
            'thumbnailUrl'  => null,
            'attachmentUrl' => $this->notifierHelper->getPostAttachmentUrl($post),
            'id'            => $this->notifierHelper->getId($category, $command->postId),
        ];

        $body = '%firstName %lastName';

        if ($unionId = $post->getUnionId()) {
            $union = $this->unionRepository->getById($unionId);
            $body = $union->getName();
        } else {
            $user = $this->userRepository->getById($post->getUserId());
        }

        foreach ($tokens as $info) {
            if (isset($user) && $user instanceof User) {
                $firstNameText = $this->notifierHelper->getFirstName($user, $info['locale']);
                $lastNameText = $this->notifierHelper->getLastName($user, $info['locale']);
            }

            $this->pusher->send(
                new Command(
                    bundleId: $info['bundleId'],
                    platform: $info['platform'],
                    locale: $info['locale'],
                    tokens: $info['tokens'],
                    title: $body, // todo
                    body: 'Новая запись', // todo
                    subtitle: null,
                    category: $category->value,
                    thread: $thread,
                    data: $data,
                    badge: null,
                    sound: NotifierSound::DEFAULT->value,
                    translateParams: [
                        '%firstName' => $firstNameText ?? null,
                        '%lastName'  => $lastNameText ?? null,
                    ]
                )
            );
        }
    }
}
