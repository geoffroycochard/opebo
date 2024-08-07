<?php
declare(strict_types=1);

// src/Notifier/CustomLoginLinkNotification
namespace App\Notifier;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Notifier\Message\EmailMessage;
use Symfony\Component\Notifier\Recipient\EmailRecipientInterface;
use Symfony\Component\Security\Http\LoginLink\LoginLinkDetails;
use Symfony\Component\Security\Http\LoginLink\LoginLinkNotification;

class CustomLoginLinkNotification extends LoginLinkNotification
{
    private LoginLinkDetails $loginLinkDetails;

    public function __construct(LoginLinkDetails $loginLinkDetails, string $subject, array $channels = [], string $adminEmail = null)
    {
        parent::__construct($loginLinkDetails,$subject, $channels);

        $this->loginLinkDetails = $loginLinkDetails;
    }

    public function asEmailMessage(EmailRecipientInterface $recipient, ?string $transport = null): ?EmailMessage
    {
        $duration = $this->loginLinkDetails->getExpiresAt()->getTimestamp() - time();
        $durationString = floor($duration / 60).' minute'.($duration > 60 ? 's' : '');
        if (($hours = $duration / 3600) >= 1) {
            $durationString = floor($hours).' heure'.($hours >= 2 ? 's' : '');
        }

        $email = (new TemplatedEmail())
        ->from('ope@orleans-metropole.fr')
            ->to($recipient->getEmail())
            ->subject('Connexion à votre compte')
            ->htmlTemplate('emails/login_link_email.html.twig')
            ->context([
                'duration' => $durationString,
                'login_link' => $this->loginLinkDetails->getUrl()
            ])
        ;

        return new EmailMessage($email);
    }
}
