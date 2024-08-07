<?php
declare(strict_types=1);

namespace App\EventSubscriber;

use App\Service\ActivityLogger;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\Event;

class WorkflowLoggerSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private ActivityLogger $logger,
    ) {
    }

    public function onLeave(Event $event): void
    {
        $this->logger->setLog(
            'workflow', 
            get_class($event->getSubject()), 
            $event->getSubject()->getId(),
            'Transition',
            sprintf(
                '(id: "%s") performed transition "%s" from "%s" to "%s"',
                $event->getSubject()->getId(),
                $event->getTransition()->getName(),
                implode(', ', array_keys($event->getMarking()->getPlaces())),
                implode(', ', $event->getTransition()->getTos())
            )
        );
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'workflow.sponsorship.leave' => 'onLeave',
            'workflow.lead.leave' => 'onLeave',
        ];
    }
}
