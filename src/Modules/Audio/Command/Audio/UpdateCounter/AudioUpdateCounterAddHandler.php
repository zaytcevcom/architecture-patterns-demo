<?php

declare(strict_types=1);

namespace App\Modules\Audio\Command\Audio\UpdateCounter;

use App\Http\Action\Unifier\Audio\AudioUnifier;
use App\Modules\Audio\Entity\Audio\AudioRepository;
use App\Modules\Audio\Entity\AudioUser\AudioUserRepository;
use App\Modules\Audio\Service\AudioRealtimeNotifier;
use ZayMedia\Shared\Components\Flusher;

final readonly class AudioUpdateCounterAddHandler
{
    public function __construct(
        private AudioRepository $audioRepository,
        private AudioUserRepository $audioUserRepository,
        private Flusher $flusher,
        private AudioRealtimeNotifier $audioRealtimeNotifier,
        private AudioUnifier $audioUnifier,
    ) {}

    public function handle(int $id): void
    {
        $audio = $this->audioRepository->getById($id);

        $audio->setCountAdd(
            $this->audioUserRepository->countByAudio($audio)
        );

        $this->flusher->flush();

        $this->audioRealtimeNotifier->update(
            audioId: $audio->getId(),
            data: $this->audioUnifier->unifyOne(null, $audio->toArray())
        );
    }
}
