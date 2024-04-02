<?php

declare(strict_types=1);

namespace App\Modules\Identity\Command\Restore\CodeConfirm;

use App\Modules\Identity\Entity\Restore\RestoreRepository;
use ZayMedia\Shared\Components\Flusher;
use ZayMedia\Shared\Http\Exception\DomainExceptionModule;

final readonly class IdentityRestoreCodeConfirmHandler
{
    public function __construct(
        private RestoreRepository $restoreRepository,
        private Flusher $flusher
    ) {}

    public function handle(IdentityRestoreCodeConfirmCommand $command): void
    {
        $restore = $this->restoreRepository->findByUniqueId($command->uniqueId);

        if (!$restore) {
            throw new DomainExceptionModule(
                module: 'identity',
                message: 'error.restore.code_confirm.restore_not_found',
                code: 1
            );
        }

        if (!$restore->isValidCode($command->code)) {
            throw new DomainExceptionModule(
                module: 'identity',
                message: 'error.restore.code_confirm.restore_invalid_code',
                code: 2
            );
        }

        $restore->confirm();

        $this->flusher->flush();
    }
}
