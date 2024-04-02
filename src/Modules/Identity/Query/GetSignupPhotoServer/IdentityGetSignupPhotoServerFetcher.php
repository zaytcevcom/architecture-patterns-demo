<?php

declare(strict_types=1);

namespace App\Modules\Identity\Query\GetSignupPhotoServer;

use App\Modules\Storage\Entity\StorageHostRepository;

final class IdentityGetSignupPhotoServerFetcher
{
    private const TYPE = 14;

    public function __construct(
        private readonly StorageHostRepository $storageHostRepository
    ) {}

    /** @return array{url: string} */
    public function fetch(): array
    {
        $storageHost = $this->storageHostRepository->getByRandom();

        return [
            'url' => $storageHost->getHost() . '/v1/photos/' . self::TYPE,
        ];
    }
}
