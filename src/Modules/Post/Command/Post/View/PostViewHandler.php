<?php

declare(strict_types=1);

namespace App\Modules\Post\Command\Post\View;

use App\Modules\Identity\Entity\User\UserRepository;
use App\Modules\Post\Entity\Post\PostRepository;
use App\Modules\Post\Entity\PostView\PostView;
use App\Modules\Post\Entity\PostView\PostViewRepository;
use App\Modules\Post\Event\Post\Viewed\PostViewedData;
use App\Modules\Post\Event\Post\Viewed\PostViewedEvent;
use ZayMedia\Shared\Components\Flusher;

final readonly class PostViewHandler
{
    public function __construct(
        private UserRepository $userRepository,
        private PostRepository $postRepository,
        private PostViewRepository $postViewRepository,
        private PostViewedEvent $postViewedEvent,
        private Flusher $flusher
    ) {}

    public function handle(PostViewCommand $command): void
    {
        $post = $this->postRepository->getById($command->postId);
        $user = $this->userRepository->getById($command->userId);

        $view = $this->postViewRepository->findLastByPostAndUserIds(
            postId: $post->getId(),
            userId: $user->getId()
        );

        if ($view && !$view->isEnoughTime()) {
            return;
        }

        $view = PostView::create(
            postId: $post->getId(),
            userId: $user->getId()
        );

        $this->postViewRepository->add($view);

        $this->flusher->flush();

        $this->postViewedEvent->handle(
            new PostViewedData(
                userId: $user->getId(),
                postId: $post->getId()
            )
        );
    }
}
