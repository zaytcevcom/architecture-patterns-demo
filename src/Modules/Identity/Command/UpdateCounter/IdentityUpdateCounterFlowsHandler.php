<?php

declare(strict_types=1);

namespace App\Modules\Identity\Command\UpdateCounter;

use App\Modules\Flow\Entity\Flow\FlowRepository;
use App\Modules\Identity\Entity\User\UserRepository;
use ZayMedia\Shared\Components\Flusher;

final readonly class IdentityUpdateCounterFlowsHandler
{
    public function __construct(
        private UserRepository $userRepository,
        private FlowRepository $flowRepository,
        private Flusher $flusher
    ) {}

    public function handle(int $id): void
    {
        $user = $this->userRepository->getById($id);

        $user->setCountFlows(
            $this->flowRepository->countByUserId($user->getId())
        );

        $this->flusher->flush();
    }
}
