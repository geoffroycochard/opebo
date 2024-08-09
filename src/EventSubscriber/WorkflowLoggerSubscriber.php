<?php
declare(strict_types=1);

namespace App\EventSubscriber;

use App\Service\ActivityLogger;
use Doctrine\Common\Util\ClassUtils;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\Event;

class WorkflowLoggerSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private ActivityLogger $logger,
    ) {
    }

    public function onLeaveSponsorship(Event $event): void
    {
        $tansitionsToLog = ['to_in_progress', 'to_ended'];
        if (in_array($event->getTransition()->getName(), $tansitionsToLog)) {
            $this->logger->logTransition($event);
        }
    }

    public function onLeaveLead(Event $event): void
    {
        $tansitionsToLog = ['to_not_satisfiable', 'to_sponsorized', 'to_archived'];
        if (in_array($event->getTransition()->getName(), $tansitionsToLog)) {
            $this->logger->logTransition($event);
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'workflow.sponsorship.leave' => 'onLeaveSponsorship',
            'workflow.lead.leave' => 'onLeaveLead',
        ];
    }
}
