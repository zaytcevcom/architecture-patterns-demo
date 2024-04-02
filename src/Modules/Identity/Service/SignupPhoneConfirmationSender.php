<?php

declare(strict_types=1);

namespace App\Modules\Identity\Service;

use App\Modules\Identity\Entity\User\Fields\Phone;
use App\Modules\System\Command\Sms\Create\SmsCreateCommand;
use App\Modules\System\Command\Sms\Create\SmsCreateHandler;
use App\Modules\System\Entity\IpAddressBlacklist\IpAddressBlacklistRepository;
use App\Modules\System\Entity\Sms\SmsRepository;
use Symfony\Contracts\Translation\TranslatorInterface;
use ZayMedia\Shared\Components\SmsSender\SmsSender;

final readonly class SignupPhoneConfirmationSender
{
    public function __construct(
        private TranslatorInterface $translator,
        private SmsSender $smsSender,
        private SmsRepository $smsRepository,
        private SmsCreateHandler $smsCreateHandler,
        private IpAddressBlacklistRepository $ipAddressBlacklistRepository,
    ) {}

    public function send(Phone $phone, string $code, string $ipReal, string $ipAddress): void
    {
        if ($this->ipAddressBlacklistRepository->findByIpAddress($ipAddress)) {
            return;
        }

        if (!$this->smsRepository->canSendByIpAddress($ipAddress)) {
            return;
        }

        $text = trim($this->translator->trans('sms.signup.confirm.text', ['%d' => $code], 'identity'));

        $this->smsCreateHandler->handle(
            new SmsCreateCommand(
                phone: $phone->getValue(),
                ipReal: $ipReal,
                ipAddress: $ipAddress,
                text: $text
            )
        );

        $this->smsSender->send(
            number: $phone->getValue(),
            text: $text,
            ip: $ipAddress
        );
    }
}
