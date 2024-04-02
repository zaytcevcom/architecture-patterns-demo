<?php

declare(strict_types=1);

namespace App\Modules\Audio\Entity\AudioPlaylistAudio;

use App\Modules\Audio\Entity\Audio\Audio;
use App\Modules\Audio\Entity\AudioPlaylist\AudioPlaylist;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

class AudioPlaylistAudioRepository
{
    /**
     * @var EntityRepository<AudioPlaylistAudio>
     */
    private EntityRepository $repo;
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->repo = $em->getRepository(AudioPlaylistAudio::class);
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

    public function findByAudioPlaylistAndAudio(AudioPlaylist $audioPlaylist, Audio $audio): ?AudioPlaylistAudio
    {
        return $this->repo->findOneBy([
            'audioPlaylist' => $audioPlaylist,
            'audio' => $audio,
        ]);
    }

    public function add(AudioPlaylistAudio $audioPlaylistAudio): void
    {
        $this->em->persist($audioPlaylistAudio);
    }

    public function remove(AudioPlaylistAudio $audioPlaylistAudio): void
    {
        $this->em->remove($audioPlaylistAudio);
    }
}
