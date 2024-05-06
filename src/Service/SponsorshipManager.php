<?php
declare(strict_types=1);

namespace App\Service;

use App\Entity\Lead;
use App\Entity\Sponsorship;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\SponsorshipRepository;
use Symfony\Component\Workflow\WorkflowInterface;
use Symfony\Component\DependencyInjection\Attribute\Target;

final class SponsorshipManager
{
    
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly SponsorshipRepository $sponsorshipRepository,
        #[Target('sponsorship')]
        private readonly WorkflowInterface $sponsorshipWorkflow,
        #[Target('lead')]
        private readonly WorkflowInterface $leadWorkflow
    )
    {}

    /**
     * 1 - pass sp to in_progress
     * 2 - unlock 
     */
    public function adminProposal(Sponsorship $sponsorship): void
    {
        // Pass sponsorship to proposed
        $this->sponsorshipWorkflow->apply($sponsorship, 'to_in_progress');

        // Pass origin lead [request,proposal] to proposed
        $request = $sponsorship->getRequest();
        $this->leadWorkflow->apply($request, 'to_sponsorized');
        $proposal = $sponsorship->getProposal();
        $this->leadWorkflow->apply($proposal, 'to_sponsorized');

        // unlock all other
        /** @var QueryBuilder $qb  */
        $qb = $this->sponsorshipRepository->createQueryBuilder('s');
        $qb
            ->where('s.request = :request')
            ->andWhere('s.proposal != :proposal')
            ->setParameters([
                'request' => $request,
                'proposal' => $proposal,
            ])
        ;
        /** @var Sponsorship $sponsorship */
        foreach ($qb->getQuery()->getResult() as $sponsorship) {
            $this->leadWorkflow->apply($sponsorship->getProposal(), 'to_free');
            $this->entityManager->remove($sponsorship);
        }

        $this->entityManager->flush();
    }

    public function validate(
        Sponsorship $sponsorship, 
        string $transition
    )
    {
        // Pass sponsorship to transition
        $this->sponsorshipWorkflow->apply($sponsorship, $transition);
        $this->entityManager->flush();
    }


}
