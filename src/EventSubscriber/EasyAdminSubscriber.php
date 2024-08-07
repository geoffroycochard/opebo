<?php
declare(strict_types=1);

namespace App\EventSubscriber;

use App\Entity\BlogPost;
use App\Entity\Sponsorship;
use App\Service\SponsorshipManager;
use EasyCorp\Bundle\EasyAdminBundle\Event\AfterEntityPersistedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityPersistedEvent;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\WorkflowInterface;

class EasyAdminSubscriber implements EventSubscriberInterface
{
    private $slugger;

    public function __construct(
        private SponsorshipManager $sponsorshipManager,
        #[Target('lead')]
        private WorkflowInterface $leadWorkflow
    )
    {}

    public static function getSubscribedEvents()
    {
        return [
            BeforeEntityPersistedEvent::class => ['setSponsorshipData'],
            AfterEntityPersistedEvent::class => ['startSponsorshipWorkflow'],
        ];
    }

    public function setSponsorshipData(BeforeEntityPersistedEvent $event)
    {
        $entity = $event->getEntityInstance();

        if (!($entity instanceof Sponsorship)) {
            return;
        }

        $entity->setStatus('initialized');
        $entity->setReminder((new \DateTime())->modify('+2 months'));

        $this->leadWorkflow->apply($entity->getProposal(), 'to_blocked');
        $this->leadWorkflow->apply($entity->getRequest(), 'to_blocked');
        
    }

    public function startSponsorshipWorkflow(AfterEntityPersistedEvent $event)
    {
        $entity = $event->getEntityInstance();

        if (!($entity instanceof Sponsorship)) {
            return;
        }

        if ($entity->getStatus() === 'initialized') {
            $this->sponsorshipManager->adminProposal($entity);
        }
    }
}