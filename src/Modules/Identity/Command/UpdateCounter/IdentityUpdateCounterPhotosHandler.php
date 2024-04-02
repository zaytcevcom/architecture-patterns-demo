<?php

declare(strict_types=1);

namespace App\Modules\Identity\Command\UpdateCounter;

use App\Modules\Identity\Entity\User\UserRepository;
use App\Modules\Photo\Entity\Photo\PhotoRepository;
use ZayMedia\Shared\Components\Flusher;

final readonly class IdentityUpdateCounterPhotosHandler
{
    public function __construct(
        private UserRepository $userRepository,
        private PhotoRepository $photoRepository,
        private Flusher $flusher
    ) {}

    public function handle(int $id): void
    {
        $user = $this->userRepository->getById($id);

        $user->setCountPhotos(
            $this->photoRepository->countByUser($user)
        );

        $this->flusher->flush();
    }
}
