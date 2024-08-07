<?php
declare(strict_types=1);

namespace App\EventListener;

use App\Entity\Person;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Events;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Mailer\MailerInterface;

#[AsEntityListener(event: Events::postPersist, method: 'postPersist', entity: Person::class)]
class PersonLeadCreationNotifier
{
    public function __construct(
        private readonly MailerInterface $mailer,
        #[Autowire('%admin_email%')] private string $adminEmail,
    ) {}

    public function postPersist(Person $person, PostPersistEventArgs $event): void
    {
        $email = (new TemplatedEmail())
            ->from($this->adminEmail)
            ->to(new \Symfony\Component\Mime\Address($person->getEmail()))
            ->subject('Confirmation de votre inscription')
            ->htmlTemplate('emails/registration.html.twig')
            ->context([
                'person' => $person
            ])
        ;
        $this->mailer->send($email);

    }
}