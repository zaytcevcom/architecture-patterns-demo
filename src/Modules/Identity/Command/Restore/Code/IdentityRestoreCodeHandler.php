<?php

declare(strict_types=1);

namespace App\Modules\Identity\Command\Restore\Code;

use App\Modules\Identity\Entity\Restore\RestoreRepository;
use App\Modules\Identity\Service\RestoreEmailConfirmationSender;
use App\Modules\Identity\Service\RestorePhoneConfirmationSender;
use ZayMedia\Shared\Components\Flusher;
use ZayMedia\Shared\Http\Exception\DomainExceptionModule;

final readonly class IdentityRestoreCodeHandler
{
    public function __construct(
        private RestoreRepository $restoreRepository,
        private Flusher $flusher,
        private RestoreEmailConfirmationSender $senderEmail,
        private RestorePhoneConfirmationSender $senderPhone
    ) {}

    public function handle(IdentityRestoreCodeCommand $command): array
    {
        $restore = $this->restoreRepository->findByUniqueId($command->uniqueId);

        if (!$restore) {
            throw new DomainExceptionModule(
                module: 'identity',
                message: 'error.restore.code.restore_not_found',
                code: 1
            );
        }

        if (!$restore->isValidInterval()) {
            throw new DomainExceptionModule(
                module: 'identity',
                message: 'error.restore.code.too_frequent_requests_for_a_confirmation_code',
                code: 2
            );
        }

        $restore->generateCode();

        $this->flusher->flush();

        $email = $restore->getEmail();
        $phone = $restore->getPhone();

        if (null !== $email) {
            $this->senderEmail->send(
                email: $email,
                code: $restore->getCode()->getValue()
            );
        } elseif (null !== $phone) {
            $this->senderPhone->send(
                phone: $phone,
                code: $restore->getCode()->getValue(),
                ipReal: $command->ipReal,
                ipAddress: $command->ipAddress
            );
        }

        return [
            'response' => 1,
            'interval' => $restore->getTimeInterval(),
        ];
    }
}
