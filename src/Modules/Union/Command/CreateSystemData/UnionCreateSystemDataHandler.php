<?php

declare(strict_types=1);

namespace App\Modules\Union\Command\CreateSystemData;

use App\Modules\Photo\Entity\PhotoAlbum\PhotoAlbum;
use App\Modules\Photo\Entity\PhotoAlbum\PhotoAlbumRepository;
use App\Modules\Union\Command\Management\CreatePhotoSave\CreatePhotoSaveCommand;
use App\Modules\Union\Command\Management\CreatePhotoSave\CreatePhotoSaveHandler;
use App\Modules\Union\Entity\UnionSection\UnionSection;
use App\Modules\Union\Entity\UnionSection\UnionSectionRepository;
use ZayMedia\Shared\Components\Flusher;

final readonly class UnionCreateSystemDataHandler
{
    public function __construct(
        private CreatePhotoSaveHandler $createPhotoSaveHandler,
        private PhotoAlbumRepository $photoAlbumRepository,
        private UnionSectionRepository $unionSectionRepository,
        private Flusher $flusher
    ) {}

    public function handle(UnionCreateSystemDataCommand $command): void
    {
        $this->unionSectionRepository->add(UnionSection::create($command->unionId));

        $this->photoAlbumRepository->add(PhotoAlbum::createSystemProfileForUnion($command->unionId));
        $this->photoAlbumRepository->add(PhotoAlbum::createSystemLoadedForUnion($command->unionId));
        $this->photoAlbumRepository->add(PhotoAlbum::createSystemPostsForUnion($command->unionId));

        $this->flusher->flush();

        if (null !== $command->photoHost && null !== $command->photoFileId) {
            $this->createPhotoSaveHandler->handle(
                new CreatePhotoSaveCommand(
                    userId: $command->userId,
                    unionId: $command->unionId,
                    host: $command->photoHost,
                    fileId: $command->photoFileId
                )
            );
        }
    }
}
