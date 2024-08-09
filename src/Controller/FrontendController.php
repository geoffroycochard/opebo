<?php

namespace App\Controller;

use App\Entity\Lead;
use App\Entity\Person;
use App\Entity\Sponsorship;
use App\Notifier\CustomLoginLinkNotification;
use App\Repository\LeadRepository;
use App\Repository\PersonRepository;
use App\Repository\SponsorRepository;
use App\Service\AccountRemover;
use App\Service\SponsorshipManager;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Component\Notifier\Recipient\Recipient;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\LoginLink\LoginLinkHandlerInterface;
use Symfony\Component\Security\Http\LoginLink\LoginLinkNotification;

class FrontendController extends AbstractController
{
    #[Route('/app/login_check', name: 'app_frontend_login_check')]
    public function check(): never
    {
        throw new \LogicException('This code should never be reached');
    }

    #[Route('/app/connect', name: 'app_frontend_login')]
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
            if (!$user) {
                $notifier->send(new Notification(sprintf('Impossible de trouver ce compte : %s.', $email), ['browser']));
                return $this->render('frontend/request_login_link.html.twig');
            }

            // create a login link for $user this returns an instance
            // of LoginLinkDetails
            $loginLinkDetails = $loginLinkHandler->createLoginLink($user);
            $loginLink = $loginLinkDetails->getUrl();

            // ... send the link and return a response (see next section)
            // create a notification based on the login link details
            $notification = new CustomLoginLinkNotification(
                $loginLinkDetails,
                'Connect to yout manager'    // email subject
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

    #[Route('/app', name: 'app_frontend_dashboard')]
    public function dashboard(Request $request, PersonRepository $personRepository, LeadRepository $leadRepository): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED');
        /** @var Person $person */
        $person = $this->getUser();
        $type = get_class($person);

        
        $canDelete = true;
        foreach ($person->getLeads() as $lead) {
            foreach ($lead->getSponsorships() as $sponsoship) {
                if ($sponsoship->getStatus() === 'in_progress') {
                    $canDelete = false;
                }
            }
        }

        //dd($canDelete);
        return $this->render('frontend/dashboard.html.twig', [
            'person' => $person,
            'canDelete' => $canDelete,
            'type' => $type
        ]);
    }

    #[Route('/app/sponsorship/back/{sponsorship}/{transition}', name: 'app_frontend_sponsorship_back')]
    public function sponsorshipBack(
        Sponsorship $sponsorship,
        string $transition,
        SponsorshipManager $sponsorshipManager,
        NotifierInterface $notifier
    )
    {

        $notifier->send(new Notification('Votre parrainage a pris fin suite à votre demande.', ['browser']));

        $sponsorshipManager->validate(
            $sponsorship,
            $transition
        );


        return $this->redirectToRoute('app_frontend_dashboard');
    }

    #[Route('/app/delete', name: 'app_frontend_delete')]
    public function deleteAccount(
        AccountRemover $accountRemover,
        NotifierInterface $notifier,
        Security $security
    )
    {
        /** @var Person $person */
        $person = $this->getUser();
        $accountRemover->remove($person);
        $security->logout(false);
        $notifier->send(new Notification('Votre compte a été supprimé.', ['browser']));

        return $this->redirectToRoute('app_frontend_login');
    }
}
