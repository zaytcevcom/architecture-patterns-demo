<?php

declare(strict_types=1);

namespace App\Modules\Post\Command\Post\UnPin;

use App\Modules\Post\Entity\Post\PostRepository;
use Symfony\Component\Finder\Exception\AccessDeniedException;

final readonly class PostUnPinHandler
{
    public function __construct(
        private PostRepository $postRepository,
    ) {}

    public function handle(PostUnPinCommand $command): void
    {
        $post = $this->postRepository->getById($command->postId);

        // todo: permissions
        //        if ($post->getUserId() !== $command->userId) {
        //            throw new AccessDeniedException();
        //        }

        $this->postRepository->unPinAll($post->getOwnerId());
    }
}
