<?php

namespace App\Controller\Admin;

use App\Entity\Proposal;
use App\Entity\Request;
use App\Entity\Sponsor;
use App\Entity\Sponsorship;
use App\Entity\Student;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractDashboardController
{
    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        return $this->render('admin/dashboard.html.twig');
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('OpebO');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');

        yield MenuItem::section('Personne');
        yield MenuItem::linkToCrud('Student', 'fa fa-graduation-cap', Student::class);
        yield MenuItem::linkToCrud('Sponsor', 'fa fa-user', Sponsor::class);

        yield MenuItem::section('Lead');
        yield MenuItem::linkToCrud('Request', 'fa fa-comment', Request::class);
        yield MenuItem::linkToCrud('Proposal', 'fa fa-user', Proposal::class);

        yield MenuItem::section('Sponsorship');
        yield MenuItem::linkToCrud('Sponsorship', 'fa fa-leaf', Sponsorship::class);

        
    }
}
