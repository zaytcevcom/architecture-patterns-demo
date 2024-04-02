<?php

declare(strict_types=1);

namespace App\Modules\Post\Command\Post\Hide;

use App\Modules\Identity\Entity\User\UserRepository;
use App\Modules\Post\Entity\Post\PostRepository;
use App\Modules\Post\Entity\PostHide\PostHide;
use App\Modules\Post\Entity\PostHide\PostHideRepository;
use ZayMedia\Shared\Components\Flusher;

final readonly class PostHideHandler
{
    public function __construct(
        private UserRepository $userRepository,
        private PostRepository $postRepository,
        private PostHideRepository $postHideRepository,
        private Flusher $flusher
    ) {}

    public function handle(PostHideCommand $command): void
    {
        $post = $this->postRepository->getById($command->postId);
        $user = $this->userRepository->getById($command->userId);

        $postHide = $this->postHideRepository->findByPostAndUserIds(
            postId: $post->getId(),
            userId: $user->getId()
        );

        if ($postHide !== null) {
            return;
        }

        $postHide = PostHide::create(
            postId: $post->getId(),
            userId: $user->getId()
        );

        $this->postHideRepository->add($postHide);

        $this->flusher->flush();
    }
}
