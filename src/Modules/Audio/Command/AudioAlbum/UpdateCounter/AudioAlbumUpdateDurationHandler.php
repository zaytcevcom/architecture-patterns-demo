<?php

declare(strict_types=1);

namespace App\Modules\Audio\Command\AudioAlbum\UpdateCounter;

use App\Modules\Audio\Entity\AudioAlbum\AudioAlbumRepository;
use App\Modules\Audio\Entity\AudioAlbumUser\AudioAlbumUserRepository;
use ZayMedia\Shared\Components\Flusher;

final readonly class AudioAlbumUpdateDurationHandler
{
    public function __construct(
        private AudioAlbumRepository $audioAlbumRepository,
        private AudioAlbumUserRepository $audioAlbumUserRepository,
        private Flusher $flusher
    ) {}

    public function handle(int $id): void
    {
        $audioAlbum = $this->audioAlbumRepository->getById($id);

        $audioAlbum->setDuration(
            $this->audioAlbumUserRepository->durationByAudioAlbum($audioAlbum)
        );

        $this->flusher->flush();
    }
}
