<?php

declare(strict_types=1);

namespace App\Modules\Post\Command\Post\Pin;

use App\Modules\Post\Entity\Post\PostRepository;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use ZayMedia\Shared\Components\Flusher;

final readonly class PostPinHandler
{
    public function __construct(
        private PostRepository $postRepository,
        private Flusher $flusher
    ) {}

    public function handle(PostPinCommand $command): void
    {
        $post = $this->postRepository->getById($command->postId);

        // todo: permissions
        //        if ($post->getUserId() !== $command->userId) {
        //            throw new AccessDeniedException();
        //        }

        $this->postRepository->unPinAll($post->getOwnerId());

        $post->pin();
        $this->postRepository->add($post);

        $this->flusher->flush();
    }
}
