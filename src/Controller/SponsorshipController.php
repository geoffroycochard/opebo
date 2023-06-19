<?php

namespace App\Controller;

use App\Repository\SponsorshipRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SponsorshipController extends AbstractController
{
    #[Route('/sponsorship', name: 'app_sponsorship')]
    public function index(SponsorshipRepository $sponsorshipRepository): Response
    {
        $requestId = 28;

        $sponsorships = $sponsorshipRepository->findBy(
            ['request' => $requestId],
            ['score' => 'DESC']
        );

        return $this->render('sponsorship/index.html.twig', [
            'sponsorships' => $sponsorships,
            'requestId' => $requestId
        ]);
    }
}
