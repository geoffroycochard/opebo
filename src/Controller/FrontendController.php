<?php

namespace App\Controller;

use App\Entity\Lead;
use App\Entity\Person;
use App\Entity\Sponsorship;
use App\Repository\LeadRepository;
use App\Repository\PersonRepository;
use App\Repository\SponsorRepository;
use App\Service\SponsorshipManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Component\Notifier\Recipient\Recipient;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\LoginLink\LoginLinkHandlerInterface;
use Symfony\Component\Security\Http\LoginLink\LoginLinkNotification;

#[Route('/app')]
class FrontendController extends AbstractController
{
    #[Route('/login_check', name: 'login_check')]
    public function check(): Response
    {
        return $this->redirectToRoute('app_frontend_dashboard');    
    }

    #[Route('/login', name: 'login')]
    public function requestLoginLink(
        NotifierInterface $notifier,
        LoginLinkHandlerInterface $loginLinkHandler, 
        PersonRepository $userRepository, 
        Request $request
        ): Response
    {
        // check if form is submitted
        if ($request->isMethod('POST')) {
            // load the user in some way (e.g. using the form input)
            $email = $request->getPayload()->get('email');
            $user = $userRepository->findOneBy(['email' => $email]);
            // create a login link for $user this returns an instance
            // of LoginLinkDetails
            $loginLinkDetails = $loginLinkHandler->createLoginLink($user);
            $loginLink = $loginLinkDetails->getUrl();

            // ... send the link and return a response (see next section)
            // create a notification based on the login link details
            $notification = new LoginLinkNotification(
                $loginLinkDetails,
                'Connect to yout manager' // email subject
            );
            // create a recipient for this user
            $recipient = new Recipient($user->getEmail());

            // send the notification to the user
            $notifier->send($notification, $recipient);

            // render a "Login link is sent!" page
            return $this->render('frontend/login_link_sent.html.twig');
        }

        // if it's not submitted, render the form to request the "login link"
        return $this->render('frontend/request_login_link.html.twig');
    }

    #[Route('/', name: 'app_frontend_dashboard')]
    public function dashboard(Request $request, PersonRepository $personRepository, LeadRepository $leadRepository): Response
    {
        /** @var Person $person */
        $person = $this->getUser();
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
