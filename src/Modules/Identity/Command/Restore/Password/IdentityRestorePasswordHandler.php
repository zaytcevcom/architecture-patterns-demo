<?php

declare(strict_types=1);

namespace App\Modules\Identity\Command\Restore\Password;

use App\Modules\Identity\Entity\Restore\RestoreRepository;
use App\Modules\Identity\Entity\User\Fields\Password;
use App\Modules\Identity\Entity\User\UserRepository;
use App\Modules\Identity\Service\PasswordHasher;
use ZayMedia\Shared\Components\Flusher;
use ZayMedia\Shared\Http\Exception\DomainExceptionModule;

final readonly class IdentityRestorePasswordHandler
{
    public function __construct(
        private UserRepository $userRepository,
        private RestoreRepository $restoreRepository,
        private Flusher $flusher,
        private PasswordHasher $hasher,
    ) {}

    public function handle(IdentityRestorePasswordCommand $command): void
    {
        $restore = $this->restoreRepository->findByUniqueId($command->uniqueId);

        if (!$restore) {
            throw new DomainExceptionModule(
                module: 'identity',
                message: 'error.restore.password.restore_not_found',
                code: 1
            );
        }

        if (!$restore->isConfirmed()) {
            throw new DomainExceptionModule(
                module: 'identity',
                message: 'error.restore.password.code_not_confirm',
                code: 2
            );
        }

        $userId = $restore->getUser()?->getId();

        if ($userId === null || !$user = $this->userRepository->findById($userId)) {
            throw new DomainExceptionModule(
                module: 'identity',
                message: 'error.restore.password.user_not_found',
                code: 3
            );
        }

        $user->setPassword($this->hasher->hash((new Password(value: $command->password))->getValue()));
        $restore->done();

        $this->flusher->flush();
    }
}
