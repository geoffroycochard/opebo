<?php
declare(strict_types=1);

namespace App\EventListener;

use App\Entity\Person;
use App\Repository\PersonRepository;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Events;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Security\Http\LoginLink\LoginLinkHandlerInterface;

#[AsEntityListener(event: Events::postPersist, method: 'postPersist', entity: Person::class)]
class PersonLeadCreationNotifier
{
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly PersonRepository $personRepository,
        private readonly LoginLinkHandlerInterface $loginLinkHandler,
        #[Autowire('%admin_email%')] private string $adminEmail,
    ) {}

    public function postPersist(Person $person, PostPersistEventArgs $event): void
    {

        // Send poposal to sponsor
        $user = $this->personRepository->findOneBy(['email' => $person->getEmail()]);
        $loginLinkDetails = $this->loginLinkHandler->createLoginLink($user);
        $loginLink = $loginLinkDetails->getUrl();

        $email = (new TemplatedEmail())
            ->from($this->adminEmail)
            ->to(new \Symfony\Component\Mime\Address($person->getEmail()))
            ->subject('Confirmation de votre inscription')
            ->htmlTemplate('emails/registration.html.twig')
            ->context([
                'person' => $person,
                'login_link' => $loginLink
            ])
        ;
        $this->mailer->send($email);

    }
}