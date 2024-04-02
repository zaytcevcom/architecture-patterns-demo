<?php

declare(strict_types=1);

namespace App\Modules\Post\Entity\PostView;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

class PostViewRepository
{
    /** @var EntityRepository<PostView> */
    private EntityRepository $repo;
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->repo = $em->getRepository(PostView::class);
        $this->em = $em;
    }

    public function countByPost(int $postId): int
    {
        return $this->repo->count([
            'postId' => $postId,
        ]);
    }

    public function findLastByPostAndUserIds(int $postId, int $userId): ?PostView
    {
        return $this->repo->findOneBy(
            ['postId' => $postId, 'userId' => $userId],
            ['time' => 'DESC']
        );
    }

    public function add(PostView $postView): void
    {
        $this->em->persist($postView);
    }

    public function remove(PostView $postView): void
    {
        $this->em->remove($postView);
    }
}
