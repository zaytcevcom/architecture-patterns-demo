<?php

declare(strict_types=1);

namespace App\Modules\Identity\Command\PhotoSave;

use App\Modules\Identity\Entity\User\Fields\PhotoFileId;
use App\Modules\Identity\Entity\User\Fields\PhotoHost;
use App\Modules\Identity\Entity\User\UserRepository;
use App\Modules\Photo\Command\PhotoAlbum\UpdateCounter\PhotoAlbumUpdateCounterPhotosHandler;
use App\Modules\Photo\Entity\Photo\Fields;
use App\Modules\Photo\Entity\Photo\Photo;
use App\Modules\Photo\Entity\Photo\PhotoRepository;
use App\Modules\Photo\Entity\PhotoAlbum\PhotoAlbumRepository;
use App\Modules\Storage\Entity\StorageHostRepository;
use App\Modules\Storage\Service\StoragePhoto;
use ZayMedia\Shared\Components\Flusher;
use ZayMedia\Shared\Http\Exception\DomainExceptionModule;

final readonly class PhotoSaveHandler
{
    public function __construct(
        private UserRepository $userRepository,
        private PhotoRepository $photoRepository,
        private PhotoAlbumRepository $photoAlbumRepository,
        private PhotoAlbumUpdateCounterPhotosHandler $photoAlbumUpdateCounterPhotosHandler,
        private StorageHostRepository $storageHostRepository,
        private StoragePhoto $storagePhoto,
        private Flusher $flusher
    ) {}

    public function handle(PhotoSaveCommand $command): Photo
    {
        $user = $this->userRepository->getById($command->userId);

        $storageHost = $this->storageHostRepository->getByHost($command->host);

        $this->storagePhoto->setHost($command->host);
        $this->storagePhoto->setApiKey($storageHost->getSecret());

        /** @var array{sizes: array, original: array, crop_square: array|null, crop_custom: array|null, fields: array|null}|null $info */
        $info = $this->storagePhoto->getInfo($command->fileId);

        if (empty($info)) {
            throw new DomainExceptionModule(
                module: 'identity',
                message: 'error.photo.failed_to_save_image',
                code: 4
            );
        }

        if (!isset($info['fields']['user_id']) || (int)$info['fields']['user_id'] !== $command->userId) {
            throw new DomainExceptionModule(
                module: 'identity',
                message: 'error.photo.failed_to_save_image',
                code: 4
            );
        }

        // Check duplication
        $photo = $this->photoRepository->findByFileId(new Fields\PhotoFileId($command->fileId));

        if (!empty($photo)) {
            throw new DomainExceptionModule(
                module: 'identity',
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

        $albumProfile = $this->photoAlbumRepository->getSystemProfileByUserId($user->getId());

        $photo = Photo::createUserPhoto(
            user: $user,
            album: $albumProfile,
            photo: new Fields\Photo(json_encode($sizes)),
            photoHost: new Fields\PhotoHost($command->host),
            photoFileId: new Fields\PhotoFileId($command->fileId)
        );

        $albumProfile->setCover($photo);
        $this->photoAlbumRepository->add($albumProfile);

        $this->photoRepository->add($photo);
        $this->flusher->flush();

        $this->storagePhoto->markUse($command->fileId);

        if ($photoObjectStr = $photo->getPhoto()?->getValue()) {
            $photoObject = (array)json_decode($photoObjectStr, true);

            /**
             * @var int|string $k
             * @var array<int|string,string>|string $v
             */
            foreach ($photoObject as $k => $v) {
                if (\in_array($k, ['crop_square', 'crop_custom'], true)) {
                    /** @var array<int,array<int|string,string>|string> $v */
                    foreach ($v as $key => $val) {
                        if (\is_array($val) && isset($val['src'])) {
                            if (\is_array($photoObject[$k])) {
                                $photoObject[$k][$key] = $val['src'];
                            }
                        }
                    }
                }

                if (\is_array($v) && isset($v['src'])) {
                    $photoObject[$k] = $v['src'];
                }
            }

            $user->setPhoto(new \App\Modules\Identity\Entity\User\Fields\Photo(json_encode($photoObject)));
        }

        $user->setPhotoHost(new PhotoHost($photo->getPhotoHost()?->getValue()));
        $user->setPhotoFileId(new PhotoFileId($photo->getPhotoFileId()?->getValue()));
        $user->setPhotoId($photo->getId());

        $this->flusher->flush();

        $this->photoAlbumUpdateCounterPhotosHandler->handle($albumProfile->getId());

        return $photo;
    }
}
