<?php

declare(strict_types=1);

namespace App\Modules\Audio\Command\AudioPlaylist\Create;

use App\Modules\Audio\Entity\AudioPlaylist\AudioPlaylist;
use App\Modules\Audio\Entity\AudioPlaylist\AudioPlaylistRepository;
use App\Modules\Audio\Event\AudioPlaylist\AudioPlaylistEventPublisher;
use App\Modules\Audio\Event\AudioPlaylist\AudioPlaylistQueue;
use App\Modules\Union\Entity\Union\UnionRepository;
use ZayMedia\Shared\Components\Flusher;

final readonly class AudioPlaylistCreateHandler
{
    public function __construct(
        private AudioPlaylistRepository $audioPlaylistRepository,
        private UnionRepository $unionRepository,
        private Flusher $flusher,
        private AudioPlaylistEventPublisher $eventPublisher,
    ) {}

    public function handle(AudioPlaylistCreateCommand $command): AudioPlaylist
    {
        $union = $this->unionRepository->getById($command->unionId);

        $audioPlaylist = AudioPlaylist::create(
            unionId: $union->getId(),
            name: $command->name,
            artists: $command->artists
        );

        $this->audioPlaylistRepository->add($audioPlaylist);

        $this->flusher->flush();

        $this->eventPublisher->handle(AudioPlaylistQueue::CREATED, $audioPlaylist->getId());

        return $audioPlaylist;
    }
}
