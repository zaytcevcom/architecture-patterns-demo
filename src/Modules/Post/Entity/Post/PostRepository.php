<?php

declare(strict_types=1);

namespace App\Modules\Post\Entity\Post;

use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use ZayMedia\Shared\Http\Exception\DomainExceptionModule;

class PostRepository
{
    /**
     * @var EntityRepository<Post>
     */
    private EntityRepository $repo;
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->repo = $em->getRepository(Post::class);
        $this->em = $em;
    }

    public function countByPost(int $postId): int
    {
        return $this->repo
            ->matching(
                Criteria::create()
                    ->andWhere(Criteria::expr()->eq('postId', $postId))
                    ->andWhere(Criteria::expr()->eq('hide', '0'))
                    ->andWhere(Criteria::expr()->isNull('deletedAt'))
            )
            ->count();
    }

    public function countByFlow(int $flowId): int
    {
        return $this->repo
            ->matching(
                Criteria::create()
                    ->andWhere(Criteria::expr()->eq('flowId', $flowId))
                    ->andWhere(Criteria::expr()->eq('hide', '0'))
                    ->andWhere(Criteria::expr()->isNull('deletedAt'))
            )
            ->count();
    }

    public function countByUserId(int $userId): int
    {
        return $this->repo
            ->matching(
                Criteria::create()
                    ->andWhere(Criteria::expr()->eq('ownerId', $userId))
                    ->andWhere(Criteria::expr()->eq('hide', '0'))
                    ->andWhere(Criteria::expr()->isNull('deletedAt'))
            )
            ->count();
    }

    public function countByUnionId(int $unionId): int
    {
        return $this->repo
            ->matching(
                Criteria::create()
                    ->andWhere(Criteria::expr()->eq('ownerId', -1 * $unionId))
                    ->andWhere(Criteria::expr()->eq('hide', '0'))
                    ->andWhere(Criteria::expr()->isNull('deletedAt'))
            )
            ->count();
    }

    public function countTodayByUserId(int $userId): int
    {
        $timeFrom = strtotime(date('Y-m-d'));

        return $this->repo
            ->matching(
                Criteria::create()
                    ->andWhere(Criteria::expr()->eq('ownerId', $userId))
                    ->andWhere(Criteria::expr()->gt('createdAt', $timeFrom))
                    ->andWhere(Criteria::expr()->eq('hide', '0'))
                    ->andWhere(Criteria::expr()->isNull('deletedAt'))
            )
            ->count();
    }

    public function countTodayByUnionId(int $unionId): int
    {
        $timeFrom = strtotime(date('Y-m-d'));

        return $this->repo
            ->matching(
                Criteria::create()
                    ->andWhere(Criteria::expr()->eq('ownerId', -1 * $unionId))
                    ->andWhere(Criteria::expr()->gt('createdAt', $timeFrom))
                    ->andWhere(Criteria::expr()->eq('hide', '0'))
                    ->andWhere(Criteria::expr()->isNull('deletedAt'))
            )
            ->count();
    }

    public function getById(int $id): Post
    {
        if (!$post = $this->findById($id)) {
            throw new DomainExceptionModule(
                module: 'post',
                message: 'error.post.post_not_found',
                code: 1
            );
        }

        return $post;
    }

    public function findById(int $id): ?Post
    {
        return $this->repo->findOneBy([
            'id' => $id,
            'hide' => 0,
            'deletedAt' => null,
        ]);
    }

    public function getDeletedById(int $id): Post
    {
        if (!$post = $this->findDeletedById($id)) {
            throw new DomainExceptionModule(
                module: 'post',
                message: 'error.post.post_not_found',
                code: 1
            );
        }

        return $post;
    }

    public function findDeletedById(int $id): ?Post
    {
        return $this->repo->findOneBy([
            'id' => $id,
        ]);
    }

    public function findFirstByFlowId(int $flowId): ?Post
    {
        return $this->repo->findOneBy([
            'flowId' => $flowId,
            'hide' => 0,
            'deletedAt' => null,
        ], ['id' => 'ASC']);
    }

    public function findByUniqueTime(int $uniqueTime, int $ownerId): ?Post
    {
        return $this->repo->findOneBy([
            'uniqueTime' => $uniqueTime,
            'ownerId' => $ownerId,
            'hide' => 0,
            'deletedAt' => null,
        ]);
    }

    public function isRepostedByUser(int $postId, int $userId): bool
    {
        return (bool)$this->repo->findOneBy([
            'postId' => $postId,
            'ownerId' => $userId,
            'hide' => 0,
            'deletedAt' => null,
        ]);
    }

    public function unPinAll(int $ownerId): void
    {
        $this->em->createQueryBuilder()
            ->update(Post::class, 'p')
            ->set('p.isPinned', '0')
            ->andWhere('p.ownerId = :ownerId')
            ->setParameter(':ownerId', $ownerId)
            ->getQuery()->execute();
    }

    public function add(Post $post): void
    {
        $this->em->persist($post);
    }

    public function remove(Post $post): void
    {
        $this->em->remove($post);
    }
}
