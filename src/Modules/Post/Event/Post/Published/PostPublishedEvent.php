<?php

declare(strict_types=1);

namespace App\Modules\Post\Event\Post\Published;

use App\Http\Action\Unifier\Post\PostUnifier;
use App\Modules\AutoPosting\Events\Published;
use App\Modules\Contact\Query\Notification\GetSubscribes\ContactNotificationGetSubscribesFetcher;
use App\Modules\Contact\Query\Notification\GetSubscribes\ContactNotificationGetSubscribesQuery;
use App\Modules\Identity\Command\UpdateCounter\IdentityUpdateCounterPostsHandler;
use App\Modules\Identity\Service\UserRealtimeNotifier;
use App\Modules\Notifier\Command\Post\PostPublished\PostPublishedCommand;
use App\Modules\Notifier\Command\Post\PostPublished\PostPublishedHandler;
use App\Modules\Post\Entity\Post\Post;
use App\Modules\Post\Entity\Post\PostRepository;
use App\Modules\Union\Command\UpdateCounter\UnionUpdateCounterPostsHandler;
use App\Modules\Union\Query\Notification\GetSubscribes\UnionNotificationGetSubscribesFetcher;
use App\Modules\Union\Query\Notification\GetSubscribes\UnionNotificationGetSubscribesQuery;
use App\Modules\Union\Service\UnionRealtimeNotifier;
use Doctrine\DBAL\Exception;

final readonly class PostPublishedEvent
{
    public function __construct(
        private PostRepository $postRepository,
        private IdentityUpdateCounterPostsHandler $identityUpdateCounterPostsHandler,
        private UnionUpdateCounterPostsHandler $unionUpdateCounterPostsHandler,
        private UserRealtimeNotifier $userRealtimeNotifier,
        private UnionRealtimeNotifier $unionRealtimeNotifier,
        private UnionNotificationGetSubscribesFetcher $unionNotificationGetSubscribesFetcher,
        private ContactNotificationGetSubscribesFetcher $contactNotificationGetSubscribesFetcher,
        private PostUnifier $postUnifier,
        private PostPublishedHandler $postPublishedHandler,
        private Published\PostPublishedPublisher $autoPostingPostPublishedPublisher,
    ) {}

    /** @throws Exception */
    public function handle(PostPublishedData $command): void
    {
        $post = $this->postRepository->getById($command->postId);

        $this->updateCounters($post);
        $this->realtime($post);
        $this->notification($post);
        $this->autoPosting($post, $command->socialIds);
    }

    private function updateCounters(Post $post): void
    {
        if ($unionId = $post->getUnionId()) {
            $this->unionUpdateCounterPostsHandler->handle($unionId);
        } else {
            $this->identityUpdateCounterPostsHandler->handle($post->getUserId());
        }
    }

    private function realtime(Post $post): void
    {
        if ($unionId = $post->getUnionId()) {
            $this->unionRealtimeNotifier->newPost(
                unionId: $unionId,
                data: $this->postUnifier->unifyOne(null, $post->toArray())
            );
        } else {
            $this->userRealtimeNotifier->newPost(
                userId: $post->getUserId(),
                data: $this->postUnifier->unifyOne(null, $post->toArray())
            );
        }
    }

    /** @throws Exception */
    private function notification(Post $post): void
    {
        if ($post->getDate() < time() - 10 * 60) {
            return;
        }

        if ($unionId = $post->getUnionId()) {
            $userIds = $this->unionNotificationGetSubscribesFetcher->fetch(
                new UnionNotificationGetSubscribesQuery(
                    unionId: $unionId
                )
            );
        } else {
            $userIds = $this->contactNotificationGetSubscribesFetcher->fetch(
                new ContactNotificationGetSubscribesQuery(
                    userId: $post->getUserId()
                )
            );
        }

        /** @var int $userId */
        foreach ($userIds as $userId) {
            $this->postPublishedHandler->handle(
                new PostPublishedCommand(
                    userId: $userId,
                    postId: $post->getId()
                )
            );
        }
    }

    private function autoPosting(Post $post, array $socialIds): void
    {
        if ($post->getDate() < time() - 18 * 3600) {
            return;
        }

        $this->autoPostingPostPublishedPublisher->handle(
            new Published\PostPublishedData(
                postId: $post->getId(),
                socialIds: $socialIds
            )
        );
    }
}
