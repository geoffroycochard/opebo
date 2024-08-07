<?php
declare(strict_types=1);

namespace App\EventSubscriber;

use App\Entity\Sponsor;
use App\Entity\Student;
use App\Entity\Sponsorship;
use App\Repository\PersonRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\Mime\Address;
use Symfony\Component\Security\Http\LoginLink\LoginLinkHandlerInterface;
use Symfony\Component\Workflow\Event\Event;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Workflow\Event\TransitionEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Workflow\WorkflowInterface;

class WorkflowMalingSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly PersonRepository $personRepository,
        private readonly LoginLinkHandlerInterface $loginLinkHandler,
        private readonly EntityManagerInterface $entityManager,
        #[Target('lead')]
        private WorkflowInterface $leadWorkflow,
        #[Autowire('%admin_email%')] private string $adminEmail,
    ) {}

    /**
     * @var TransitionEvent $event
     */
    public function toInProgress(Event $event): void
    {
        /** @var Sponsorship $sponsorship */
        $sponsorship = $event->getSubject();
        /** @var Student $student */
        $student = $sponsorship->getRequest()->getPerson();
        /** @var Sponsor $sponsor */
        $sponsor = $sponsorship->getProposal()->getPerson();

        // Send poposal to sponsor
        $user = $this->personRepository->findOneBy(['email' => $sponsor->getEmail()]);
        $loginLinkDetails = $this->loginLinkHandler->createLoginLink($user);
        $loginLink = $loginLinkDetails->getUrl();
        $email = (new TemplatedEmail())
            ->from($this->adminEmail)
            ->to(new Address($sponsor->getEmail()))
            ->subject('Votre parrainage a commencé !')
            ->htmlTemplate('emails/inprogress/sponsor.html.twig')
            ->context([
                'student' => $student,
                'sponsor' => $sponsor,
                'sponsorship' => $sponsorship,
                'login_link' => $loginLink
            ])
        ;
        try {
            $this->mailer->send($email);
        } catch (TransportExceptionInterface $e) {

        }

        // Send poposal to student
        $user = $this->personRepository->findOneBy(['email' => $student->getEmail()]);
        $loginLinkDetails = $this->loginLinkHandler->createLoginLink($user);
        $loginLink = $loginLinkDetails->getUrl();
        $email = (new TemplatedEmail())
        ->from($this->adminEmail)
            ->to(new Address($student->getEmail()))
            ->subject('Votre demande d\'accompagnement a commencé')
            ->htmlTemplate('emails/inprogress/student.html.twig')
            ->context([
                'student' => $student,
                'sponsor' => $sponsor,
                'sponsorship' => $sponsorship,
                'login_link' => $loginLink
            ])
        ;
        try {
            $this->mailer->send($email);
        } catch (TransportExceptionInterface $e) {
        }
    }

    /**
     * @var TransitionEvent $event
     */
    public function toEnded(Event $event): void
    {
        /** @var Sponsorship $sponsorship */
        $sponsorship = $event->getSubject();
        /** @var Student $student */
        $student = $sponsorship->getRequest()->getPerson();
        /** @var Sponsor $sponsor */
        $sponsor = $sponsorship->getProposal()->getPerson();

        // Send info to sponsor
        $email = (new TemplatedEmail())
            ->from($this->adminEmail)
            ->to(new Address($sponsor->getEmail()))
            ->subject('Votre parrainage est terminé')
            ->htmlTemplate('emails/ended/sponsor.html.twig')
            ->context([
                'student' => $student,
                'sponsor' => $sponsor,
                'sponsorship' => $sponsorship
            ])
        ;
        try {
            $this->mailer->send($email);
        } catch (TransportExceptionInterface $e) {

        }

        // Send poposal to student
        $email = (new TemplatedEmail())
        ->from($this->adminEmail)
            ->to(new Address($student->getEmail()))
            ->subject('Votre accompagnement est terminé')
            ->htmlTemplate('emails/ended/student.html.twig')
            ->context([
                'student' => $student,
                'sponsor' => $sponsor,
                'sponsorship' => $sponsorship
            ])
        ;
        try {
            $this->mailer->send($email);
        } catch (TransportExceptionInterface $e) {
        }

        $this->leadWorkflow->apply($sponsorship->getRequest(), 'to_archived');
        $this->leadWorkflow->apply($sponsorship->getProposal(), 'to_free');
        $this->entityManager->flush();

    }

    public static function getSubscribedEvents(): array
    {
        return [
            'workflow.sponsorship.transition.to_in_progress' => 'toInProgress',
            'workflow.sponsorship.transition.to_ended' => 'toEnded'
        ];
    }
}
