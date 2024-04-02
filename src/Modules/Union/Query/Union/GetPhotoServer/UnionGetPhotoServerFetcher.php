<?php

declare(strict_types=1);

namespace App\Modules\Union\Query\Union\GetPhotoServer;

use App\Modules\Storage\Entity\StorageHostRepository;

final class UnionGetPhotoServerFetcher
{
    private const TYPE = 11;

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
