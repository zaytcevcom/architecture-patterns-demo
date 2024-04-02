<?php

declare(strict_types=1);

namespace App\Modules\Union\Command\Management\CreatePhotoSave;

use App\Modules\Identity\Entity\User\UserRepository;
use App\Modules\Photo\Command\PhotoAlbum\UpdateCounter\PhotoAlbumUpdateCounterPhotosHandler;
use App\Modules\Photo\Entity\Photo\PhotoRepository;
use App\Modules\Photo\Entity\PhotoAlbum\PhotoAlbumRepository;
use App\Modules\Storage\Entity\StorageHostRepository;
use App\Modules\Storage\Service\StoragePhoto;
use App\Modules\Union\Entity\Union\Fields\Photo;
use App\Modules\Union\Entity\Union\Fields\PhotoFileId;
use App\Modules\Union\Entity\Union\Fields\PhotoHost;
use App\Modules\Union\Entity\Union\UnionRepository;
use ZayMedia\Shared\Components\Flusher;
use ZayMedia\Shared\Http\Exception\DomainExceptionModule;

final readonly class CreatePhotoSaveHandler
{
    public function __construct(
        private UserRepository $userRepository,
        private UnionRepository $unionRepository,
        private StorageHostRepository $storageHostRepository,
        private StoragePhoto $storagePhoto,
        private PhotoAlbumRepository $photoAlbumRepository,
        private PhotoRepository $photoRepository,
        private PhotoAlbumUpdateCounterPhotosHandler $photoAlbumUpdateCounterPhotosHandler,
        private Flusher $flusher
    ) {}

    public function handle(CreatePhotoSaveCommand $command): void
    {
        $user = $this->userRepository->getById($command->userId);
        $union = $this->unionRepository->getById($command->unionId);

        $storageHost = $this->storageHostRepository->getByHost($command->host);

        $this->storagePhoto->setHost($command->host);
        $this->storagePhoto->setApiKey($storageHost->getSecret());

        /** @var array{sizes: array, original: array, crop_square: array|null, crop_custom: array|null, fields: array|null}|null $info */
        $info = $this->storagePhoto->getInfo($command->fileId);

        if (empty($info)) {
            throw new DomainExceptionModule(
                module: 'union',
                message: 'error.photo.failed_to_save_image',
                code: 4
            );
        }

        $sizes = $info['sizes'];
        $sizes['original'] = $info['original'];

        if ($info['crop_square'] !== null) {
            $sizes['crop_square'] = $info['crop_square'];
        }

        if ($info['crop_custom'] !== null) {
            $sizes['crop_custom'] = $info['crop_custom'];
        }

        $albumProfile = $this->photoAlbumRepository->getSystemProfileByUnionId($union->getId());

        $photo = \App\Modules\Photo\Entity\Photo\Photo::createUnionPhoto(
            user: $user,
            unionId: $union->getId(),
            album: $albumProfile,
            photo: new \App\Modules\Photo\Entity\Photo\Fields\Photo(json_encode($sizes)),
            photoHost: new \App\Modules\Photo\Entity\Photo\Fields\PhotoHost($command->host),
            photoFileId: new \App\Modules\Photo\Entity\Photo\Fields\PhotoFileId($command->fileId)
        );

        $albumProfile->setCover($photo);
        $this->photoAlbumRepository->add($albumProfile);

        $this->photoRepository->add($photo);
        $this->flusher->flush();

        $this->storagePhoto->markUse($command->fileId);

        $union->setPhoto(new Photo(json_encode($sizes)));
        $union->setPhotoHost(new PhotoHost($command->host));
        $union->setPhotoFileId(new PhotoFileId($command->fileId));
        $union->setPhotoId($photo->getId());

        $this->flusher->flush();

        $this->photoAlbumUpdateCounterPhotosHandler->handle($albumProfile->getId());
    }
}
