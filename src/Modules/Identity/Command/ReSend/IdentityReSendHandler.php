<?php

declare(strict_types=1);

namespace App\Modules\Identity\Command\ReSend;

use App\Modules\Identity\Entity\Temp\UserTempRepository;
use App\Modules\Identity\Service\SignupEmailConfirmationSender;
use App\Modules\Identity\Service\SignupPhoneConfirmationSender;
use ZayMedia\Shared\Components\Flusher;
use ZayMedia\Shared\Http\Exception\DomainExceptionModule;

final readonly class IdentityReSendHandler
{
    public function __construct(
        private UserTempRepository $usersTemp,
        private Flusher $flusher,
        private SignupEmailConfirmationSender $senderEmail,
        private SignupPhoneConfirmationSender $senderPhone
    ) {}

    public function handle(IdentityReSendCommand $command): array
    {
        $userTemp = $this->usersTemp->findByUniqueId($command->uniqueId);

        if (!$userTemp) {
            throw new DomainExceptionModule(
                module: 'identity',
                message: 'error.resend.user_not_found',
                code: 1
            );
        }

        if (!$userTemp->isValidInterval()) {
            throw new DomainExceptionModule(
                module: 'identity',
                message: 'error.signup.request.too_frequent_requests_for_a_confirmation_code',
                code: 2
            );
        }

        $userTemp->generateCode();
        $userTemp->setTime(time());

        $this->flusher->flush();

        $email = $userTemp->getEmail();
        $phone = $userTemp->getPhone();

        if (null !== $email) {
            $this->senderEmail->send(
                email: $email,
                code: $userTemp->getCode()->getValue()
            );
        } elseif (null !== $phone && null !== $command->ipAddress) {
            $this->senderPhone->send(
                phone: $phone,
                code: $userTemp->getCode()->getValue(),
                ipReal: $command->ipReal,
                ipAddress: $command->ipAddress
            );
        }

        return [
            'response' => 1,
            'interval' => $userTemp->getTimeInterval(),
        ];
    }
}
