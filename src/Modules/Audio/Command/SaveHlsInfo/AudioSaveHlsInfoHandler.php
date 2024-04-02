<?php

declare(strict_types=1);

namespace App\Modules\Audio\Command\SaveHlsInfo;

use App\Modules\Audio\Entity\Audio\AudioRepository;
use ZayMedia\Shared\Components\Flusher;

final readonly class AudioSaveHlsInfoHandler
{
    public function __construct(
        private AudioRepository $audioRepository,
        private Flusher $flusher,
    ) {}

    public function handle(AudioSaveHlsInfoCommand $command): void
    {
        $audio = $this->audioRepository->getBySourceFileId($command->fileId);

        $audio->setUrlHls($command->url);

        $this->audioRepository->add($audio);

        $this->flusher->flush();
    }
}
