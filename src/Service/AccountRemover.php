<?php
declare(strict_types=1);

namespace App\Service;
use App\Config\PersonStatus;
use App\Repository\ActivityRepository;
use App\Repository\PersonRepository;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\Workflow\WorkflowInterface;

final class AccountRemover
{
    private $key = 'anonymous';

    public function __construct(
        private PersonRepository $personRepository,
        private ActivityRepository $activityRepository,
        private EntityManagerInterface $entityManager,
        #[Target('sponsorship')]
        private WorkflowInterface $sponsorshipWorkflow,
        #[Target('lead')]
        private WorkflowInterface $leadWorkflow
    )
    {}

    /**
     * Summary of remove
     * @param \App\Entity\Person $person
     * @return void
     */
    public function remove(\App\Entity\Person $person): void
    {
        // Person data
        $person->setFirstname($this->key);
        $person->setLastname($this->key);
        $person->setPhone('0000000000');
        $person->setState(PersonStatus::Deactive);
        $email = sprintf('%s%d@%s.fr', $this->key, $person->getId(), $this->key);
        $person->setEmail($email);
        $this->entityManager->persist($person);

        // Lead
        foreach ($person->getLeads() as $lead) {
            $this->leadWorkflow->apply($lead, 'to_archived');
            $this->anonymiseActivities($lead);
            foreach ($lead->getSponsorships() as $sponsorship) {
                $this->anonymiseActivities($sponsorship);
            }
        }
        $this->entityManager->flush();
    }

    private function anonymiseActivities($object)
    {
        $activities = $this->activityRepository->findBy([
            'fqcn' => ClassUtils::getClass($object),
            'entityId' => $object->getId()
        ]);

        foreach ($activities as $activitie) {
            $activitie->setUser([
                'role' => 'ROLE_ANONYMOUS'
            ]);
            $this->entityManager->persist($activitie);
        }
    }
    
}
