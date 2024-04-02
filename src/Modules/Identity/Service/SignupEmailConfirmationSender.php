<?php

declare(strict_types=1);

namespace App\Modules\Identity\Service;

use App\Modules\Identity\Entity\User\Fields\Email;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email as MimeEmail;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

use function App\Components\env;

final readonly class SignupEmailConfirmationSender
{
    public function __construct(
        private TranslatorInterface $translator,
        private MailerInterface $mailer,
        private Environment $twig
    ) {}

    public function send(Email $email, string $code): void
    {
        $html = $this->twig->render('email/signup/confirm.html.twig', [
            'code' => $code,
            'year' => date('Y'),
        ]);

        $message = (new MimeEmail())
            ->from(new Address(
                env('MAILER_FROM_EMAIL'),
                env('MAILER_FROM_NAME')
            ))
            ->subject($this->translator->trans('email.signup.confirm.subject', [], 'identity'))
            ->to($email->getValue())
            ->html($html);

        $this->mailer->send(
            message: $message
        );
    }
}
