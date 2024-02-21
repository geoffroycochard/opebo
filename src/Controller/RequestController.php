<?php

namespace App\Controller;

use App\Entity\Request;
use App\Entity\Sponsorship;
use App\Repository\RequestRepository;
use App\Service\SponsorshipManager;
use App\Repository\SponsorshipRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Workflow\WorkflowInterface;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/request')]
class RequestController extends AbstractController
{
    #[Route('/', name: 'app_request_list')]
    public function list(RequestRepository $requestRepository): Response
    {
        $requests = $requestRepository->findAll();

        return $this->render('request/index.html.twig', [
            'requests' => $requests
        ]);
    }

    #[Route('/proposals/{request}', name: 'app_request_proposals')]
    public function index(Request $request, SponsorshipRepository $sponsorshipRepository): Response
    {
        $sponsorships = $sponsorshipRepository->findBy(
            ['request' => $request->getId()],
            ['score' => 'DESC']
        );

        return $this->render('sponsorship/index.html.twig', [
            'sponsorships' => $sponsorships,
            'requestId' => $request->getId()
        ]);
    }

    #[Route('/proposals/validate/{sponsorship}', name: 'app_request_proposal_validate')]
    public function validate(
        Sponsorship $sponsorship,
        SponsorshipManager $sponsorshipManager
    ): Response
    {
        $sponsorshipManager->adminProposal($sponsorship);
        return $this->redirectToRoute('app_sponsorship_show', [
            'sponsorship' => $sponsorship->getId()
        ]);
    }
}
