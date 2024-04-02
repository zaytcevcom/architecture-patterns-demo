<?php

declare(strict_types=1);

namespace App\Modules\Audio\Command\AudioAlbum\UpdateCounter;

use App\Modules\Audio\Entity\Audio\AudioRepository;
use App\Modules\Audio\Entity\AudioAlbum\AudioAlbumRepository;
use ZayMedia\Shared\Components\Flusher;

final readonly class AudioAlbumUpdateCounterAudiosHandler
{
    public function __construct(
        private AudioAlbumRepository $audioAlbumRepository,
        private AudioRepository $audioRepository,
        private Flusher $flusher
    ) {}

    public function handle(int $id): void
    {
        $audioAlbum = $this->audioAlbumRepository->getById($id);

        $audioAlbum->setAudioCount(
            $this->audioRepository->countByAudioAlbumId($audioAlbum->getId())
        );

        $this->flusher->flush();
    }
}
