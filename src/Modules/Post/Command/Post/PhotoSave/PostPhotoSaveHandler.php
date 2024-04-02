<?php

declare(strict_types=1);

namespace App\Modules\Post\Command\Post\PhotoSave;

use App\Modules\Identity\Entity\User\UserRepository;
use App\Modules\Photo\Entity\Photo\Fields;
use App\Modules\Photo\Entity\Photo\Photo;
use App\Modules\Photo\Entity\Photo\PhotoRepository;
use App\Modules\Photo\Entity\PhotoAlbum\PhotoAlbumRepository;
use App\Modules\Storage\Entity\StorageHostRepository;
use App\Modules\Storage\Service\StoragePhoto;
use App\Modules\Union\Entity\Union\UnionRepository;
use ZayMedia\Shared\Components\Flusher;
use ZayMedia\Shared\Http\Exception\DomainExceptionModule;

final readonly class PostPhotoSaveHandler
{
    public function __construct(
        private UserRepository $userRepository,
        private UnionRepository $unionRepository,
        private PhotoRepository $photoRepository,
        private PhotoAlbumRepository $photoAlbumRepository,
        private StorageHostRepository $storageHostRepository,
        private StoragePhoto $storagePhoto,
        private Flusher $flusher
    ) {}

    public function handle(PostPhotoSaveCommand $command): Photo
    {
        $user = $this->userRepository->getById($command->userId);
        $union = (!empty($command->unionId)) ? $this->unionRepository->getById($command->unionId) : null;

        // Check max limit
        if ($this->photoRepository->countByUser($user) >= Photo::limitTotal()) {
            throw new DomainExceptionModule(
                module: 'post',
                message: 'error.photo.limit_total',
                code: 2
            );
        }

        // Check daily limit
        if ($this->photoRepository->countTodayByUser($user) >= Photo::limitDaily()) {
            throw new DomainExceptionModule(
                module: 'post',
                message: 'error.photo.limit_daily',
                code: 3
            );
        }

        $storageHost = $this->storageHostRepository->getByHost($command->host);

        $this->storagePhoto->setHost($command->host);
        $this->storagePhoto->setApiKey($storageHost->getSecret());

        /** @var array{sizes: array, original: array, crop_square: array|null, crop_custom: array|null, fields: array|null}|null $info */
        $info = $this->storagePhoto->getInfo($command->fileId);

        if (empty($info)) {
            throw new DomainExceptionModule(
                module: 'post',
                message: 'error.photo.failed_to_save_image',
                code: 4
            );
        }

        if (!isset($info['fields']['owner_id']) || (int)$info['fields']['owner_id'] !== $command->userId) {
            throw new DomainExceptionModule(
                module: 'post',
                message: 'error.photo.failed_to_save_image',
                code: 4
            );
        }

        // Check duplication
        $photo = $this->photoRepository->findByFileId(new Fields\PhotoFileId($command->fileId));

        if (!empty($photo)) {
            throw new DomainExceptionModule(
                module: 'post',
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

        $unionId = $union?->getId();

        if ($unionId) {
            $photoAlbum = $this->photoAlbumRepository->getSystemPostsByUnionId($unionId);
        } else {
            $photoAlbum = $this->photoAlbumRepository->getSystemPostsByUserId($user->getId());
        }

        $photo = Photo::createPostPhoto(
            user: $user,
            unionId: $unionId,
            album: $photoAlbum,
            photo: new Fields\Photo(json_encode($sizes)),
            photoHost: new Fields\PhotoHost($command->host),
            photoFileId: new Fields\PhotoFileId($command->fileId)
        );

        if (null !== $command->postedAt) {
            $photo->setDate($command->postedAt);
            $photo->setCreatedAt($command->postedAt);
        }

        $this->photoRepository->add($photo);
        $this->flusher->flush();

        $this->storagePhoto->markUse($command->fileId);

        // Set cover
        $photoAlbum->setCover($photo);
        $this->flusher->flush();

        return $photo;
    }
}
