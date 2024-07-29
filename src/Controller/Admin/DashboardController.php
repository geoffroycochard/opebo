<?php

namespace App\Controller\Admin;

use App\Entity\Course;
use App\Entity\Domain;
use App\Entity\Establishment;
use App\Entity\Proposal;
use App\Entity\Request;
use App\Entity\Sector;
use App\Entity\Sponsor;
use App\Entity\Sponsorship;
use App\Entity\Student;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin')]
class DashboardController extends AbstractDashboardController
{
    public function __construct(
        private EntityManagerInterface $entityManager
    )
    {}

    #[Route('/', name: 'app_admin')]
    public function index(): Response
    {
        $stats = [
            'request_awaiting' => count($this->entityManager
                ->getRepository(Request::class)
                ->findBy([
                    'status' => 'free'
                ]))
            ,
            'sponsorship_from_start' => count($this->entityManager
                ->getRepository(Sponsorship::class)
                ->findAll())
            ,
            'student_from_start' => count($this->entityManager
                ->getRepository(Student::class)
                ->findAll())
            ,
            'sponsor_from_start' => count($this->entityManager
                ->getRepository(Sponsor::class)
                ->findAll())
        ];
        
        return $this->render('admin/dashboard.html.twig',[
            'statistics' => $stats
        ]);
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
        yield MenuItem::linkToCrud('Proposal', 'fa fa-receipt', Proposal::class);

        yield MenuItem::section('Sponsorship');
        yield MenuItem::linkToCrud('Sponsorship', 'fa fa-leaf', Sponsorship::class);

        yield MenuItem::section('Datas');
        yield MenuItem::linkToCrud('Establishment', 'fa fa-university', Establishment::class);
        yield MenuItem::linkToCrud('Sector', 'fa fa-bezier-curve', Sector::class);
        yield MenuItem::linkToCrud('Course', 'fa fa-person-chalkboard', Course::class);
        yield MenuItem::linkToCrud('Domain', 'fa fa-lines-leaning', Domain::class);

        
    }
}
