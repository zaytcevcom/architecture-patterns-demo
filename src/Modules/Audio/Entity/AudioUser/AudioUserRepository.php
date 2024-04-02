<?php

declare(strict_types=1);

namespace App\Modules\Audio\Entity\AudioUser;

use App\Modules\Audio\Entity\Audio\Audio;
use App\Modules\Identity\Entity\User\User;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

class AudioUserRepository
{
    /** @var EntityRepository<AudioUser> */
    private EntityRepository $repo;
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->repo = $em->getRepository(AudioUser::class);
        $this->em = $em;
    }

    public function countByAudio(Audio $audio): int
    {
        return $this->repo
            ->matching(
                Criteria::create()
                    ->andWhere(Criteria::expr()->eq('audio', $audio))
            )
            ->count();
    }

    public function countByUserId(int $userId): int
    {
        $queryBuilder = $this->em->getConnection()->createQueryBuilder();

        $result = $queryBuilder
            ->select('COUNT(*) AS count')
            ->from('audios_owners', 'ao')
            ->innerJoin('ao', 'audios', 'a', 'ao.audio_id = a.id')
            ->andWhere('a.hide = 0 && a.deleted_at IS NULL')
            ->andWhere('ao.owner_id = :userId')
            ->setParameter('userId', $userId)
            ->setFirstResult(0)
            ->fetchAssociative();

        /** @var array{count:int|null} $result */
        return $result['count'] ?? 0;
    }

    public function countTodayByUserId(int $userId): int
    {
        $timeFrom = strtotime(date('Y-m-d'));

        $queryBuilder = $this->em->getConnection()->createQueryBuilder();

        $result = $queryBuilder
            ->select('COUNT(*) AS count')
            ->from('audios_owners', 'ao')
            ->innerJoin('ao', 'audios', 'a', 'ao.audio_id = a.id')
            ->andWhere('a.hide = 0 && a.deleted_at IS NULL')
            ->andWhere('ao.owner_id = :userId')
            ->andWhere('ao.time > :timeFrom')
            ->setParameter('userId', $userId)
            ->setParameter('timeFrom', $timeFrom)
            ->setFirstResult(0)
            ->fetchAssociative();

        /** @var array{count:int|null} $result */
        return $result['count'] ?? 0;
    }

    public function findByAudioAndUser(Audio $audio, User $user): ?AudioUser
    {
        return $this->repo->findOneBy([
            'audio' => $audio,
            'user' => $user,
        ]);
    }

    public function add(AudioUser $audioUser): void
    {
        $this->em->persist($audioUser);
    }

    public function remove(AudioUser $audioUser): void
    {
        $this->em->remove($audioUser);
    }
}
