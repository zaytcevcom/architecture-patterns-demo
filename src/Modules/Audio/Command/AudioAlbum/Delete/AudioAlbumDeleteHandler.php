<?php

declare(strict_types=1);

namespace App\Modules\Audio\Command\AudioAlbum\Delete;

use App\Modules\Audio\Entity\Audio\AudioRepository;
use App\Modules\Audio\Entity\AudioAlbum\AudioAlbumRepository;
use ZayMedia\Shared\Components\Flusher;

final readonly class AudioAlbumDeleteHandler
{
    public function __construct(
        private AudioAlbumRepository $audioAlbumRepository,
        private AudioRepository $audioRepository,
        private Flusher $flusher
    ) {}

    public function handle(AudioAlbumDeleteCommand $command): void
    {
        $audioAlbum = $this->audioAlbumRepository->getById($command->albumId);
        $audioAlbum->markDeleted();

        $this->audioAlbumRepository->add($audioAlbum);
        $this->flusher->flush();

        $this->audioRepository->markDeletedByAlbum($audioAlbum);

        // todo: счетчики у сообщества
    }
}
