<?php
declare(strict_types=1);

namespace App\EventSubscriber;

use App\Entity\Sponsor;
use App\Entity\Student;
use App\Entity\Sponsorship;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Workflow\Event\Event;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Workflow\Event\TransitionEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class WorkflowMalingSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly MailerInterface $mailer
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
        $email = (new TemplatedEmail())
            ->from('hello@ope.orleans-metropole.fr')
            ->to(new Address($sponsor->getEmail()))
            ->subject('Sponsor : Sponsorship is started!')
            ->htmlTemplate('emails/inprogress/sponsor.html.twig')
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
            ->from('hello@ope.orleans-metropole.fr')
            ->to(new Address($student->getEmail()))
            ->subject('Student : Sponsorship is started!')
            ->htmlTemplate('emails/inprogress/student.html.twig')
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
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'workflow.sponsorship.transition.to_in_progress' => 'toInProgress',
        ];
    }
}
