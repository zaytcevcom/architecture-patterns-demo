<?php

declare(strict_types=1);

namespace App\Modules\Post\Command\Post\CloseComments;

use App\Modules\Post\Entity\Post\PostRepository;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use ZayMedia\Shared\Components\Flusher;

final readonly class PostCloseCommentsHandler
{
    public function __construct(
        private PostRepository $postRepository,
        private Flusher $flusher
    ) {}

    public function handle(PostCloseCommentsCommand $command): void
    {
        $post = $this->postRepository->getById($command->postId);

        // todo: permissions
        if ($post->getUserId() !== $command->userId) {
            throw new AccessDeniedException();
        }

        $post->setCloseComments($command->closeComments);

        $this->postRepository->add($post);

        $this->flusher->flush();
    }
}
