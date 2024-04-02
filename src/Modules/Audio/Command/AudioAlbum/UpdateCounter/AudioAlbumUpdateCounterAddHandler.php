<?php

declare(strict_types=1);

namespace App\Modules\Audio\Command\AudioAlbum\UpdateCounter;

use App\Http\Action\Unifier\Audio\AudioAlbumUnifier;
use App\Modules\Audio\Entity\AudioAlbum\AudioAlbumRepository;
use App\Modules\Audio\Entity\AudioAlbumUser\AudioAlbumUserRepository;
use App\Modules\Audio\Service\AudioAlbumRealtimeNotifier;
use ZayMedia\Shared\Components\Flusher;

final readonly class AudioAlbumUpdateCounterAddHandler
{
    public function __construct(
        private AudioAlbumRepository $audioAlbumRepository,
        private AudioAlbumUserRepository $audioAlbumUserRepository,
        private AudioAlbumRealtimeNotifier $audioAlbumRealtimeNotifier,
        private AudioAlbumUnifier $audioAlbumUnifier,
        private Flusher $flusher
    ) {}

    public function handle(int $id): void
    {
        $audioAlbum = $this->audioAlbumRepository->getById($id);

        $audioAlbum->setCountAdd(
            $this->audioAlbumUserRepository->countByAudioAlbum($audioAlbum)
        );

        $this->flusher->flush();

        $this->audioAlbumRealtimeNotifier->update(
            audioAlbumId: $audioAlbum->getId(),
            data: $this->audioAlbumUnifier->unifyOne(null, $audioAlbum->toArray())
        );
    }
}
