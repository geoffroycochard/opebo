<?php

namespace App\Controller;

use App\Entity\Lead;
use App\Entity\Person;
use App\Entity\Sponsorship;
use App\Service\SponsorshipManager;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/frontend')]
class FrontendController extends AbstractController
{
    #[Route('/', name: 'app_frontend')]
    public function index(): Response
    {
        return $this->render('frontend/index.html.twig', [
            'controller_name' => 'FrontendController',
        ]);
    }
    #[Route('/{person}', name: 'app_frontend_dashboard')]
    public function dashboard(Person $person): Response
    {
        return $this->render('frontend/dashboard.html.twig', [
            'person' => $person,
            'type' => get_class($person)
        ]);
    }

    #[Route('/sponsorship/back/{sponsorship}/{lead}/{transition}', name: 'app_frontend_sponsorship_back')]
    public function sponsorshipBack(
        Sponsorship $sponsorship,
        Lead $lead,
        string $transition,
        SponsorshipManager $sponsorshipManager
    )
    {
        $sponsorshipManager->validate(
            $sponsorship,
            $transition
        );

        dd($sponsorship, $lead, $transition);
        dd('stop');

        return $this->redirectToRoute('app_frontend_dashboard', [
            'person' => $lead->getPerson()->getId()
        ]);
    }
}
