<?php

declare(strict_types=1);

namespace App\Modules\Audio\Entity\AudioPlaylistUser;

use App\Modules\Audio\Entity\AudioPlaylist\AudioPlaylist;
use App\Modules\Identity\Entity\User\User;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

class AudioPlaylistUserRepository
{
    /**
     * @var EntityRepository<AudioPlaylistUser>
     */
    private EntityRepository $repo;
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->repo = $em->getRepository(AudioPlaylistUser::class);
        $this->em = $em;
    }

    public function countByAudioPlaylist(AudioPlaylist $audioPlaylist): int
    {
        return $this->repo
            ->matching(
                Criteria::create()
                    ->andWhere(Criteria::expr()->eq('audioPlaylist', $audioPlaylist))
            )
            ->count();
    }

    public function durationByAudioPlaylist(AudioPlaylist $audioPlaylist): int
    {
        $result = $this->em->getConnection()->createQueryBuilder()
            ->select('SUM(a.duration) AS duration')
            ->from('audio_playlist_audio', 'apa')
            ->innerJoin('apa', 'audios', 'a', 'apa.audio_id = a.id')
            ->andWhere('a.deleted_at IS NULL && a.hide = 0')
            ->andWhere('apa.playlist_id = :playlistId')
            ->setParameter('playlistId', $audioPlaylist->getId())
            ->setFirstResult(0)
            ->fetchAssociative();

        /** @var array{duration:string|null} $result */
        return (int)($result['duration'] ?? 0);
    }

    public function countByUserId(int $userId): int
    {
        $queryBuilder = $this->em->getConnection()->createQueryBuilder();

        $result = $queryBuilder
            ->select('COUNT(*) AS count')
            ->from('audio_playlist_user', 'apu')
            ->innerJoin('apu', 'audio_playlist', 'ap', 'apu.playlist_id = ap.id')
            ->andWhere('ap.deleted_at IS NULL')
            ->andWhere('apu.user_id = :userId')
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
            ->from('audio_playlist_user', 'apu')
            ->innerJoin('apu', 'audio_playlist', 'ap', 'apu.playlist_id = ap.id')
            ->andWhere('ap.deleted_at IS NULL')
            ->andWhere('apu.user_id = :userId')
            ->andWhere('apu.created_at > :timeFrom')
            ->setParameter('userId', $userId)
            ->setParameter('timeFrom', $timeFrom)
            ->setFirstResult(0)
            ->fetchAssociative();

        /** @var array{count:int|null} $result */
        return $result['count'] ?? 0;
    }

    public function findByAudioPlaylistAndUser(AudioPlaylist $audioPlaylist, User $user): ?AudioPlaylistUser
    {
        return $this->repo->findOneBy([
            'audioPlaylist' => $audioPlaylist,
            'user' => $user,
        ]);
    }

    public function add(AudioPlaylistUser $audioPlaylistUser): void
    {
        $this->em->persist($audioPlaylistUser);
    }

    public function remove(AudioPlaylistUser $audioPlaylistUser): void
    {
        $this->em->remove($audioPlaylistUser);
    }
}
