<?php

declare(strict_types=1);

namespace App\Modules\Post\Command\Post\Update;

use App\Http\Action\Unifier\Post\PostUnifier;
use App\Modules\Audio\Query\Audio\GetByIds\AudioGetByIdsFetcher;
use App\Modules\Audio\Query\Audio\GetByIds\AudioGetByIdsQuery;
use App\Modules\Identity\Command\UpdateCounter\IdentityUpdateCounterPostsHandler;
use App\Modules\Media\Query\Video\GetByIds\VideoGetByIdsFetcher;
use App\Modules\Media\Query\Video\GetByIds\VideoGetByIdsQuery;
use App\Modules\Photo\Query\Photo\GetByIds\PhotoGetByIdsFetcher;
use App\Modules\Photo\Query\Photo\GetByIds\PhotoGetByIdsQuery;
use App\Modules\Post\Entity\Post\Post;
use App\Modules\Post\Entity\Post\PostRepository;
use App\Modules\Post\Service\PostRealtimeNotifier;
use App\Modules\Union\Command\UpdateCounter\UnionUpdateCounterPostsHandler;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use ZayMedia\Shared\Components\Flusher;
use ZayMedia\Shared\Http\Exception\DomainExceptionModule;

final readonly class PostUpdateHandler
{
    public function __construct(
        private PhotoGetByIdsFetcher $photoGetByIdsFetcher,
        private AudioGetByIdsFetcher $audioGetByIdsFetcher,
        private VideoGetByIdsFetcher $videoGetByIdsFetcher,
        private PostRepository $postRepository,
        private IdentityUpdateCounterPostsHandler $identityUpdateCounterPostsHandler,
        private UnionUpdateCounterPostsHandler $unionUpdateCounterPostsHandler,
        private Flusher $flusher,
        private PostRealtimeNotifier $postRealtimeNotifier,
        private PostUnifier $postUnifier,
    ) {}

    public function handle(PostUpdateCommand $command): Post
    {
        $post = $this->postRepository->getById($command->postId);

        // todo: permissions
        if ($post->getUserId() !== $command->userId) {
            throw new AccessDeniedException();
        }

        if (!$post->canEdit()) {
            throw new DomainExceptionModule(
                module: 'post',
                message: 'post.post.time_to_edit_has_expired',
                code: 4
            );
        }

        $post->edit(
            message: $command->message,
            photoIds: $this->getPhotoIds($command->photoIds),
            audioIds: $this->getAudioIds($command->audioIds),
            videoIds: $this->getVideoIds($command->videoIds),
            time: $command->time,
            closeComments: $command->closeComments,
            contactsOnly: $command->contactsOnly
        );

        $this->postRepository->add($post);

        $this->flusher->flush();

        if ($post->getOwnerId() < 0) {
            $this->unionUpdateCounterPostsHandler->handle(-1 * $post->getOwnerId());
        } else {
            $this->identityUpdateCounterPostsHandler->handle($post->getOwnerId());
        }

        $this->postRealtimeNotifier->update(
            postId: $post->getId(),
            data: $this->postUnifier->unifyOne(null, $post->toArray())
        );

        return $post;
    }

    /** @return int[]|null */
    private function getPhotoIds(?array $arr): ?array
    {
        if (empty($arr)) {
            return null;
        }

        $items = [];

        /** @var array{id: int} $item */
        foreach ($this->photoGetByIdsFetcher->fetch(new PhotoGetByIdsQuery($arr)) as $item) {
            $items[] = $item['id'];
        }

        if (empty($items)) {
            return null;
        }

        return $items;
    }

    /** @return int[]|null */
    private function getAudioIds(?array $arr): ?array
    {
        if (empty($arr)) {
            return null;
        }

        $items = [];

        /** @var array{id: int} $item */
        foreach ($this->audioGetByIdsFetcher->fetch(new AudioGetByIdsQuery($arr)) as $item) {
            $items[] = $item['id'];
        }

        if (empty($items)) {
            return null;
        }

        return $items;
    }

    /** @return int[]|null */
    private function getVideoIds(?array $arr): ?array
    {
        if (empty($arr)) {
            return null;
        }

        $items = [];

        /** @var array{id: int} $item */
        foreach ($this->videoGetByIdsFetcher->fetch(new VideoGetByIdsQuery($arr)) as $item) {
            $items[] = $item['id'];
        }

        if (empty($items)) {
            return null;
        }

        return $items;
    }
}
