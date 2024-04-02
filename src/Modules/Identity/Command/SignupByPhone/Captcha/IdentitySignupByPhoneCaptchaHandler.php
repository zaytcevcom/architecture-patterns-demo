<?php

declare(strict_types=1);

namespace App\Modules\Identity\Command\SignupByPhone\Captcha;

use App\Components\LoCaptcha;
use App\Modules\Identity\Entity\Temp\UserTempRepository;
use App\Modules\Identity\Service\SignupPhoneConfirmationSender;
use ZayMedia\Shared\Components\Flusher;
use ZayMedia\Shared\Http\Exception\DomainExceptionModule;

final readonly class IdentitySignupByPhoneCaptchaHandler
{
    public function __construct(
        private UserTempRepository $usersTemp,
        private SignupPhoneConfirmationSender $sender,
        private LoCaptcha $captcha,
        private Flusher $flusher,
    ) {}

    public function handle(IdentitySignupByPhoneCaptchaCommand $command): array
    {
        $userTemp = $this->usersTemp->findByUniqueId($command->uniqueId);

        if (!$userTemp) {
            throw new DomainExceptionModule(
                module: 'identity',
                message: 'error.signup.confirm.user_not_found',
                code: 1
            );
        }

        $phone = $userTemp->getPhone();
        $ipReal = $userTemp->getIpReal();
        $ipAddress = $userTemp->getIpAddress();

        if (null === $phone || null === $ipReal || null === $ipAddress) {
            throw new DomainExceptionModule(
                module: 'identity',
                message: 'error.signup.confirm.user_not_found',
                code: 1
            );
        }

        if (!$this->captcha->validate($command->uniqueId, $command->code)) {
            return [
                'success' => 0,
                'captcha' => $this->captcha->get($command->uniqueId),
            ];
        }

        if (!$userTemp->isCaptchaVerified()) {
            $canSend = $phone->getValue() !== '78888888888';

            $userTemp->captchaVerify();
            $userTemp->setTime(time());
            $this->flusher->flush();

            if ($canSend) {
                $this->sender->send(
                    phone: $phone,
                    code: $userTemp->getCode()->getValue(),
                    ipReal: $ipReal,
                    ipAddress: $ipAddress
                );
            }
        }

        return [
            'success'  => 1,
            'interval' => $userTemp->getTimeInterval(),
        ];
    }
}
