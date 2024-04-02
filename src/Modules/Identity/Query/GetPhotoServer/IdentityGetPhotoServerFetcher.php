<?php

declare(strict_types=1);

namespace App\Modules\Identity\Query\GetPhotoServer;

use App\Modules\Storage\Entity\StorageHostRepository;

final class IdentityGetPhotoServerFetcher
{
    private const TYPE = 13;

    public function __construct(
        private readonly StorageHostRepository $storageHostRepository
    ) {}

    public function fetch(): array
    {
        $storageHost = $this->storageHostRepository->getByRandom();

        return [
            'url' => $storageHost->getHost() . '/v1/photos/' . self::TYPE,
        ];
    }
}
