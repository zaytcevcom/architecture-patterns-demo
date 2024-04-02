<?php

declare(strict_types=1);

namespace App\Modules\Post\Command\PostComment\Update;

use App\Http\Action\Unifier\Post\PostCommentUnifier;
use App\Modules\Audio\Query\Audio\GetByIds\AudioGetByIdsFetcher;
use App\Modules\Audio\Query\Audio\GetByIds\AudioGetByIdsQuery;
use App\Modules\Media\Query\Video\GetByIds\VideoGetByIdsFetcher;
use App\Modules\Media\Query\Video\GetByIds\VideoGetByIdsQuery;
use App\Modules\Photo\Query\Photo\GetByIds\PhotoGetByIdsFetcher;
use App\Modules\Photo\Query\Photo\GetByIds\PhotoGetByIdsQuery;
use App\Modules\Post\Entity\PostComment\PostCommentRepository;
use App\Modules\Post\Service\PostRealtimeNotifier;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use ZayMedia\Shared\Components\Flusher;

final readonly class PostCommentUpdateHandler
{
    public function __construct(
        private PhotoGetByIdsFetcher $photoGetByIdsFetcher,
        private AudioGetByIdsFetcher $audioGetByIdsFetcher,
        private VideoGetByIdsFetcher $videoGetByIdsFetcher,
        private PostCommentRepository $postCommentRepository,
        private PostRealtimeNotifier $postRealtimeNotifier,
        private PostCommentUnifier $postCommentUnifier,
        private Flusher $flusher,
    ) {}

    public function handle(PostCommentUpdateCommand $command): void
    {
        $postComment = $this->postCommentRepository->getById($command->commentId);

        if ($postComment->getUserId() !== $command->userId) {
            throw new AccessDeniedException();
        }

        $postComment->edit(
            message: $command->message,
            photoIds: $this->getPhotoIds($command->photoIds),
            audioIds: $this->getAudioIds($command->audioIds),
            videoIds: $this->getVideoIds($command->videoIds),
            stickerId: $command->stickerId,
        );

        $this->postCommentRepository->add($postComment);

        $this->flusher->flush();

        $this->postRealtimeNotifier->updateComment(
            postId: $postComment->getPostId(),
            data: $this->postCommentUnifier->unifyOne(null, $postComment->toArray())
        );
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
