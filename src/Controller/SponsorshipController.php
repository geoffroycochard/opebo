<?php

namespace App\Controller;

use App\Entity\Sponsorship;
use App\Repository\SponsorshipRepository;
use App\Service\SponsorshipManager;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/sponsorship')]
class SponsorshipController extends AbstractController
{
    #[Route('/show/{sponsorship}', name: 'app_sponsorship_show')]
    public function show(
        Sponsorship $sponsorship,
        SponsorshipManager $sponsorshipManager
    ): Response
    {
        return $this->render('sponsorship/show.html.twig', [
            'sponsorship' => $sponsorship
        ]);
    }
}
