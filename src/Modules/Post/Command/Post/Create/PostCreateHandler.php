<?php

declare(strict_types=1);

namespace App\Modules\Post\Command\Post\Create;

use App\Modules\Audio\Query\Audio\GetByIds\AudioGetByIdsFetcher;
use App\Modules\Audio\Query\Audio\GetByIds\AudioGetByIdsQuery;
use App\Modules\Identity\Entity\User\User;
use App\Modules\Identity\Entity\User\UserRepository;
use App\Modules\Media\Query\Video\GetByIds\VideoGetByIdsFetcher;
use App\Modules\Media\Query\Video\GetByIds\VideoGetByIdsQuery;
use App\Modules\Photo\Query\Photo\GetByIds\PhotoGetByIdsFetcher;
use App\Modules\Photo\Query\Photo\GetByIds\PhotoGetByIdsQuery;
use App\Modules\Post\Entity\Post\Post;
use App\Modules\Post\Entity\Post\PostRepository;
use App\Modules\Post\Event\Post\Published\PostPublishedData;
use App\Modules\Post\Event\Post\Published\PostPublishedPublisher;
use App\Modules\Union\Entity\Union\Union;
use App\Modules\Union\Entity\Union\UnionRepository;
use ZayMedia\Shared\Components\Flusher;
use ZayMedia\Shared\Http\Exception\DomainExceptionModule;

use function ZayMedia\Shared\Components\Functions\toArrayInt;

final readonly class PostCreateHandler
{
    public function __construct(
        private PhotoGetByIdsFetcher $photoGetByIdsFetcher,
        private AudioGetByIdsFetcher $audioGetByIdsFetcher,
        private VideoGetByIdsFetcher $videoGetByIdsFetcher,
        private UserRepository $userRepository,
        private UnionRepository $unionRepository,
        private PostRepository $postRepository,
        private PostPublishedPublisher $postPublishedPublisher,
        private Flusher $flusher
    ) {}

    public function handle(PostCreateCommand $command): Post
    {
        $user = $this->userRepository->getById($command->userId);
        $union = (!empty($command->unionId)) ? $this->unionRepository->getById($command->unionId) : null;

        if ($union !== null) {
            $this->checkLimitUnion($union);
        } else {
            $this->checkLimitUser($user);
        }

        if ($command->uniqueTime !== null) {
            $ownerId = !empty($union) ? -1 * $union->getId() : $user->getId();

            if ($this->postRepository->findByUniqueTime($command->uniqueTime, $ownerId)) {
                throw new DomainExceptionModule(
                    module: 'post',
                    message: 'error.post.duplicate_post',
                    code: 4
                );
            }
        }

        $post = Post::create(
            userId: $user->getId(),
            unionId: $union?->getId(),
            message: $command->message,
            photoIds: $this->getPhotoIds($command->photoIds),
            audioIds: $this->getAudioIds($command->audioIds),
            videoIds: $this->getVideoIds($command->videoIds),
            flowId: $command->flowId,
            time: $command->time,
            uniqueTime: $command->uniqueTime,
            closeComments: $command->closeComments,
            contactsOnly: $command->contactsOnly
        );

        if (null !== $command->postedAt) {
            $post->setDate($command->postedAt);
        }

        $this->postRepository->add($post);

        $this->flusher->flush();

        $this->postPublishedPublisher->handle(
            new PostPublishedData(
                userId: $user->getId(),
                postId: $post->getId(),
                socialIds: toArrayInt($command->socialIds ?? [])
            )
        );

        return $post;
    }

    private function checkLimitUnion(Union $union): void
    {
        // Check max limit
        if ($this->postRepository->countByUnionId($union->getId()) >= Post::limitTotal()) {
            throw new DomainExceptionModule(
                module: 'post',
                message: 'error.post.limit_total',
                code: 2
            );
        }

        // Check daily limit
        if ($this->postRepository->countTodayByUnionId($union->getId()) >= Post::limitDaily()) {
            throw new DomainExceptionModule(
                module: 'post',
                message: 'error.post.limit_daily',
                code: 3
            );
        }
    }

    private function checkLimitUser(User $user): void
    {
        // Check max limit
        if ($this->postRepository->countByUserId($user->getId()) >= Post::limitTotal()) {
            throw new DomainExceptionModule(
                module: 'post',
                message: 'error.post.limit_total',
                code: 2
            );
        }

        // Check daily limit
        if ($this->postRepository->countTodayByUserId($user->getId()) >= Post::limitDaily()) {
            throw new DomainExceptionModule(
                module: 'post',
                message: 'error.post.limit_daily',
                code: 3
            );
        }
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
