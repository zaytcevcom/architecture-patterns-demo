<?php

declare(strict_types=1);

namespace App\Modules\Post\Event\Post\Viewed;

use App\Modules\Identity\Entity\User\User;
use App\Modules\Identity\Entity\User\UserRepository;
use App\Modules\Post\Command\Post\UpdateCounter\PostUpdateCounterViewsHandler;
use App\Modules\Post\Entity\Post\Post;
use App\Modules\Post\Entity\Post\PostRepository;
use App\Modules\Post\Entity\PostView\PostView;
use App\Modules\Post\Entity\PostView\PostViewRepository;
use ZayMedia\Shared\Components\Flusher;

final readonly class PostViewedEvent
{
    public function __construct(
        private UserRepository $userRepository,
        private PostRepository $postRepository,
        private PostUpdateCounterViewsHandler $postUpdateCounterViewsHandler,
        private PostViewRepository $postViewRepository,
        private Flusher $flusher
    ) {}

    public function handle(PostViewedData $command): void
    {
        $user = $this->userRepository->getById($command->userId);
        $post = $this->postRepository->getById($command->postId);

        $this->updateCounters($post->getId());
        $this->updateSourceViews($post, $user);
    }

    private function updateCounters(int $postId): void
    {
        $this->postUpdateCounterViewsHandler->handle($postId);
    }

    private function updateSourceViews(Post $post, User $user): void
    {
        if (!$postId = $post->getPostId()) {
            return;
        }

        $view = PostView::create(
            postId: $postId,
            userId: $user->getId()
        );

        $this->postViewRepository->add($view);

        $this->flusher->flush();

        $this->postUpdateCounterViewsHandler->handle($postId);
    }
}
