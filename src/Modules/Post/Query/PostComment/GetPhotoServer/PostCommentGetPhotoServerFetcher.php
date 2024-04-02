<?php

declare(strict_types=1);

namespace App\Modules\Post\Query\PostComment\GetPhotoServer;

use App\Modules\Storage\Entity\StorageHostRepository;

final class PostCommentGetPhotoServerFetcher
{
    private const TYPE = 6;

    public function __construct(
        private readonly StorageHostRepository $storageHostRepository
    ) {}

    public static function getType(): int
    {
        return self::TYPE;
    }

    public function fetch(): array
    {
        $storageHost = $this->storageHostRepository->getByRandom();

        return [
            'url' => $storageHost->getHost() . '/v1/photos/' . self::TYPE,
        ];
    }
}
