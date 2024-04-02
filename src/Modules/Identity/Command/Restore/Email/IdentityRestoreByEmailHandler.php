<?php

declare(strict_types=1);

namespace App\Modules\Identity\Command\Restore\Email;

use App\Modules\Identity\Entity\Restore\Restore;
use App\Modules\Identity\Entity\Restore\RestoreRepository;
use App\Modules\Identity\Entity\User\Fields\Email;
use App\Modules\Identity\Entity\User\Fields\FirstName;
use App\Modules\Identity\Entity\User\Fields\LastName;
use App\Modules\Identity\Entity\User\UserRepository;
use ZayMedia\Shared\Components\Flusher;
use ZayMedia\Shared\Http\Exception\DomainExceptionModule;

final class IdentityRestoreByEmailHandler
{
    private const MAX_COUNT_DAILY = 3;

    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly Flusher $flusher,
        private readonly RestoreRepository $restoreRepository
    ) {}

    public function handle(IdentityRestoreByEmailCommand $command): array
    {
        $email      = new Email($command->email);
        $firstName  = new FirstName($command->firstName);
        $lastName   = new LastName($command->lastName);

        $restore = Restore::createByEmail($email, $firstName, $lastName);

        $countAttempts = $this->restoreRepository->countAttemptsByEmailToday($email);

        if ($countAttempts >= self::MAX_COUNT_DAILY) {
            throw new DomainExceptionModule(
                module: 'identity',
                message: 'error.restore.email.exceeded_number_of_attempts',
                code: 2
            );
        }

        $user = $this->userRepository->findByEmailForRestore($email, $firstName, $lastName);

        if ($user) {
            $restore->setUser($user);
        }

        $restore->generateUniqueId();

        $this->restoreRepository->add($restore);
        $this->flusher->flush();

        if (!$user) {
            ++$countAttempts;
            $countOfAttemptsLeft = (self::MAX_COUNT_DAILY - $countAttempts > 0) ? self::MAX_COUNT_DAILY - $countAttempts : 0;

            throw new DomainExceptionModule(
                module: 'identity',
                message: 'error.restore.email.user_not_found',
                code: 1,
                payload: ['countOfAttemptsLeft' => $countOfAttemptsLeft]
            );
        }

        return [
            'uniqueId' => $restore->getUniqueId(),
            'user' => $user,
        ];
    }
}
