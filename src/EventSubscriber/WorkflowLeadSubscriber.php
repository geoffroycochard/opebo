<?php
declare(strict_types=1);

namespace App\EventSubscriber;

use App\Entity\Sponsorship;
use App\Repository\PersonRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\Event;
use Symfony\Component\Workflow\Event\TransitionEvent;
use Symfony\Component\Workflow\WorkflowInterface;

class WorkflowLeadSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly PersonRepository $personRepository,
        private readonly EntityManagerInterface $entityManager,
        #[Target('lead')]
        private WorkflowInterface $leadWorkflow,
    ) {}


    /**
     * @var TransitionEvent $event
     */
    public function toEnded(Event $event): void
    {
        /** @var Sponsorship $sponsorship */
        $sponsorship = $event->getSubject();

        $this->leadWorkflow->apply($sponsorship->getRequest(), 'to_archived');
        $this->leadWorkflow->apply($sponsorship->getProposal(), 'to_free');
        $this->entityManager->flush();

    }

    public static function getSubscribedEvents(): array
    {
        return [
            'workflow.sponsorship.transition.to_ended' => 'toEnded'
        ];
    }
}
