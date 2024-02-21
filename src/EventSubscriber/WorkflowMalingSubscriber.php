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
    ) {
    }

    public function toProposed(Event $event): void
    {
        /** @var Sponsorship $sponsorship */
        $sponsorship = $event->getSubject();
        /** @var Student $student */
        $student = $sponsorship->getRequest()->getPerson();
        /** @var Sponsor $sponsor */
        $sponsor = $sponsorship->getProposal()->getPerson();

        // Send poposal to student
        $email = (new TemplatedEmail())
            ->from('hello@ope.orleans-metropole.fr')
            ->to(new Address($student->getEmail()))
            ->subject('Student : Sponsorhip proposal to you!')
            ->htmlTemplate('emails/proposal/student.html.twig')
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

        // Send poposal to sponsor
        $email = (new TemplatedEmail())
            ->from('hello@ope.orleans-metropole.fr')
            ->to(new Address($sponsor->getEmail()))
            ->subject('Sponsor : Sponsorhip proposal to you!')
            ->htmlTemplate('emails/proposal/sponsor.html.twig')
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

    /**
     * @var TransitionEvent $event
     */
    public function toValidate(Event $event): void
    {
        $transition = $event->getTransition()->getName();
        /** @var Sponsorship $sponsorship */
        $sponsorship = $event->getSubject();
        /** @var Student $student */
        $student = $sponsorship->getRequest()->getPerson();
        /** @var Sponsor $sponsor */
        $sponsor = $sponsorship->getProposal()->getPerson();

        if ($transition === 'to_sp_validate') {
            // Send poposal to sponsor
            $email = (new TemplatedEmail())
                ->from('hello@ope.orleans-metropole.fr')
                ->to(new Address($sponsor->getEmail()))
                ->subject('Sponsor : Get contact with student!')
                ->htmlTemplate('emails/contact/sponsor.html.twig')
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

        if ($transition === 'to_st_validate') {
            // Send poposal to sponsor
            $email = (new TemplatedEmail())
                ->from('hello@ope.orleans-metropole.fr')
                ->to(new Address($student->getEmail()))
                ->subject('Student : Get contact with sponsor!')
                ->htmlTemplate('emails/contact/student.html.twig')
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
    }

    /**
     * @var TransitionEvent $event
     */
    public function toContacted(Event $event): void
    {
        $transition = $event->getTransition()->getName();
        /** @var Sponsorship $sponsorship */
        $sponsorship = $event->getSubject();
        /** @var Student $student */
        $student = $sponsorship->getRequest()->getPerson();
        /** @var Sponsor $sponsor */
        $sponsor = $sponsorship->getProposal()->getPerson();

        if ($transition === 'to_sp_contacted') {
            // Send poposal to sponsor
            $email = (new TemplatedEmail())
                ->from('hello@ope.orleans-metropole.fr')
                ->to(new Address($sponsor->getEmail()))
                ->subject('Sponsor : Sponsorhip waiting final validation!')
                ->htmlTemplate('emails/contacted/sponsor.html.twig')
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

        if ($transition === 'to_st_contacted') {
            // Send poposal to sponsor
            $email = (new TemplatedEmail())
                ->from('hello@ope.orleans-metropole.fr')
                ->to(new Address($student->getEmail()))
                ->subject('Student : Sponsorhip waiting final validation!')
                ->htmlTemplate('emails/contacted/student.html.twig')
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
    }

    /**
     * @var TransitionEvent $event
     */
    public function toInProgress(Event $event): void
    {
        $transition = $event->getTransition()->getName();
        /** @var Sponsorship $sponsorship */
        $sponsorship = $event->getSubject();
        /** @var Student $student */
        $student = $sponsorship->getRequest()->getPerson();
        /** @var Sponsor $sponsor */
        $sponsor = $sponsorship->getProposal()->getPerson();

        if ($transition === 'to_sp_in_progress') {
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
        }

        if ($transition === 'to_st_in_progress') {
            // Send poposal to sponsor
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
    }
    
    public static function getSubscribedEvents(): array
    {
        return [
            'workflow.sponsorship.completed.to_proposed' => 'toProposed',
            'workflow.sponsorship.completed.to_st_validate' => 'toValidate',
            'workflow.sponsorship.completed.to_sp_validate' => 'toValidate',
            'workflow.sponsorship.transition.to_st_contacted' => 'toContacted',
            'workflow.sponsorship.transition.to_sp_contacted' => 'toContacted',
            'workflow.sponsorship.transition.to_st_rejected' => 'toReject',
            'workflow.sponsorship.transition.to_sp_rejected' => 'toReject',
            'workflow.sponsorship.transition.to_st_in_progress' => 'toInProgress',
            'workflow.sponsorship.transition.to_sp_in_progress' => 'toInProgress',
        ];
    }
}
