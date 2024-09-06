<?php
declare(strict_types=1);

namespace App\EventListener;

use App\Entity\Request;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Events;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Mailer\MailerInterface;

#[AsEntityListener(event: Events::postPersist, method: 'postPersist', entity: Request::class)]
class AdminLeadCreationNotifier
{
    public function __construct(
        private readonly MailerInterface $mailer,
        #[Autowire('%admin_email%')] private string $adminEmail,
    ) {}

    public function postPersist(Request $request, PostPersistEventArgs $event): void
    {
        $email = (new TemplatedEmail())
            ->from($this->adminEmail)
            ->to($this->adminEmail)
            ->subject('Nouvelle demande d\'accompagnement')
            ->htmlTemplate('emails/admin/new.html.twig')
            ->context([
                'request' => $request
            ])
        ;
        $this->mailer->send($email);

    }
}