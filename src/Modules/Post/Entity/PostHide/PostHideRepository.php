<?php

declare(strict_types=1);

namespace App\Modules\Post\Entity\PostHide;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

readonly class PostHideRepository
{
    /** @var EntityRepository<PostHide> */
    private EntityRepository $repo;

    public function __construct(
        private EntityManagerInterface $em
    ) {
        $this->repo = $em->getRepository(PostHide::class);
    }

    public function findByPostAndUserIds(int $postId, int $userId): ?PostHide
    {
        return $this->repo->findOneBy([
            'postId' => $postId,
            'userId' => $userId,
        ]);
    }

    public function add(PostHide $postHide): void
    {
        $this->em->persist($postHide);
    }

    public function remove(PostHide $postHide): void
    {
        $this->em->remove($postHide);
    }
}
