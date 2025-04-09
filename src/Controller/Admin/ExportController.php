<?php

namespace App\Controller\Admin;

use App\Entity\Person;
use App\Entity\Sponsorship;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use League\Csv\Writer;
use League\Csv\Bom;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ExportController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    public static function getEntityFqcn(): string
    {
        return Person::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        $exportPersonnes = Action::new('exportPersonnes', 'Export Personnes')
            ->linkToCrudAction('exportPersonnes')
            ->setIcon('fa fa-download')
            ->createAsGlobalAction();

        $actions->add(Crud::PAGE_INDEX, $exportPersonnes);

        return $actions;
    }

    #[Route('/admin/export', name: 'admin_export')]
    public function index(): Response
    {
        return $this->render('admin/export/index.html.twig');
    }

    #[Route('/admin/export/personnes', name: 'admin_export_personnes')]
    public function exportPersonnes(): Response
    {
        // Récupération des personnes avec leurs relations
        $persons = $this->entityManager->getRepository(Person::class)
            ->createQueryBuilder('p')
            ->leftJoin('p.leads', 'l')
            ->addSelect('l')
            ->getQuery()
            ->getResult();

        // Création du writer en mémoire
        $csv = Writer::createFromFileObject(new \SplTempFileObject());
        $csv->setOutputBOM(Bom::Utf8);

        // En-têtes
        $csv->insertOne([
            'ID',
            'Actif',
            'Civilité',
            'Genre',
            'Nom',
            'Prénom',
            'Email',
            'Téléphone',
            'Date de naissance',
            'Ville',
            'Type',
            'Établissement',
            'Sponsorship In progress',
            'Sponsorship Ended',
        ]);

        // Données
        foreach ($persons as $person) {
            $sponsorships = new ArrayCollection();
            $sponsorshipsStatus = [];
            $sponsorshipsInProgress = 0;
            $sponsorshipsEnded = 0;
            foreach ($person->getLeads() as $lead) {
                foreach (
                    $lead->getSponsorships()->filter(
                        fn (Sponsorship $sponsorship) => $sponsorship->getStatus() != 'initialized'
                    ) as $sponsorship) 
                {
                    if ($sponsorship->getStatus() == 'in_progress') {
                        $sponsorshipsInProgress++;
                    } else {
                        $sponsorshipsEnded++;
                    }
                }
            }

            $csv->insertOne([
                $person->getId(),
                $person->getState()->value,
                $person->getCivility()->value,
                $person->getGender()->value,
                $person->getLastname(),
                $person->getFirstname(),
                $person->getEmail(),
                $person->getPhone(),
                $person->getBirthDate()?->format('Y-m-d'),
                $person->getCity(),
                $person instanceof \App\Entity\Student ? 'Étudiant' : 'Parrain',
                $person instanceof \App\Entity\Student ? $person->getEstablishment() ? $person->getEstablishment()->getName() : '' : '',
                $sponsorshipsInProgress,
                $sponsorshipsEnded,
            ]);
        }
        
        // Création de la réponse
        $response = new Response($csv->toString());
        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="persons_sponsorships_' . date('Y-m-d') . '.csv"');

        return $response;
    }

    #[Route('/admin/export/sponsorships', name: 'admin_export_sponsorships')]
    public function exportSponsorships(): Response
    {
        // Récupération des sponsorships avec leurs relations
        $sponsorships = $this->entityManager->getRepository(Sponsorship::class)
            ->createQueryBuilder('s')
            ->leftJoin('s.request', 'r')
            ->leftJoin('s.proposal', 'p')
            ->leftJoin('r.person', 'st')
            ->leftJoin('p.person', 'sp')
            ->addSelect('r')
            ->addSelect('p')
            ->addSelect('st')
            ->addSelect('sp')
            ->getQuery()
            ->getResult();

        // Création du writer en mémoire
        $csv = Writer::createFromFileObject(new \SplTempFileObject());
        $csv->setOutputBOM(Bom::Utf8);

        // En-têtes
        $csv->insertOne([
            'ID',
            'Étudiant',
            'Parrain',
            'Statut',
            'Date de début',
            'Date de mise à jour',
        ]);
        // Données
        foreach ($sponsorships as $sponsorship) {
            $csv->insertOne([
                $sponsorship->getId(),
                $sponsorship->getRequest()?->getPerson()?->getFullName(),
                $sponsorship->getProposal()?->getPerson()?->getFullName(),
                $sponsorship->getStatus(),
                $sponsorship->getCreatedAt()?->format('Y-m-d'),
                $sponsorship->getUpdatedAt()?->format('Y-m-d')
            ]);
        }
        
        // Création de la réponse
        $response = new Response($csv->toString());
        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="sponsorships.csv"');

        return $response;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle('index', 'Exports')
            ->setEntityLabelInPlural('Exports')
            ->setEntityLabelInSingular('Export')
            ->showEntityActionsInlined();
    }
} 