<?php

namespace App\Controller\Admin;

use App\Config\Objective;
use App\Entity\Proposal;
use App\Entity\Request;
use App\Entity\Sponsorship;
use App\Repository\ProposalRepository;
use App\Repository\RequestRepository;
use App\Service\SponsorshipManager;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ArrayFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\Workflow\WorkflowInterface;

class SponsorshipCrudController extends AbstractCrudController
{
    public function __construct(
        #[Target('sponsorship')]
        private WorkflowInterface $sponsorshipWorkflow,
        private SponsorshipManager $sponsorshipManager,
        private AdminUrlGenerator $adminUrlGenerator,
    )
    {}


    public static function getEntityFqcn(): string
    {
        return Sponsorship::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->overrideTemplate('crud/detail', 'admin/crud/sponsorship/detail.html.twig')
            ->setSearchFields(
                [
                    'request.person.lastname', 
                    'request.person.firstname', 
                    'proposal.person.lastname', 
                    'proposal.person.firstname'
                ]
            )
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            ChoiceField::new('status')
                ->setChoices($this->sponsorshipWorkflow->getDefinition()->getPlaces())
                ->hideOnForm()
            ,
            AssociationField::new('request.person')
                ->setCrudController(StudentCrudController::class)
                ->hideOnForm()
            ,
            AssociationField::new('proposal.person')
                ->setCrudController(SponsorCrudController::class)
                ->hideOnForm()
            ,
            AssociationField::new('request')
                ->setCrudController(RequestCrudController::class)
                ->setFormTypeOption('query_builder', function (RequestRepository $er): QueryBuilder {
                        return $er->createQueryBuilder('r')
                            ->where('r.status = :status')
                            ->setParameter('status', 'free')
                        ;
                    }
                )
                ->setFormTypeOption('choice_label', function(Request $request){
                    return $request->getPerson()->getFullname() . '('. $request->getStatus().')';
                })
                ->hideOnIndex()
            ,
            AssociationField::new('proposal')
                ->setCrudController(ProposalCrudController::class)
                ->setFormTypeOption('query_builder', function (ProposalRepository $er): QueryBuilder {
                        return $er->createQueryBuilder('p')
                            ->where('p.status = :status')
                            ->setParameter('status', 'free')
                        ;
                    }
                )
                ->setFormTypeOption('choice_label', function(Proposal $proposal){
                    return $proposal->getPerson()->getFullname(). '('. $proposal->getStatus().')';
                })
                ->hideOnIndex()
            ,
            NumberField::new('score'),
            DateTimeField::new('reminder')->hideOnForm()
        ];
    }

    public function configureActions(Actions $actions): Actions
    {
        $sw = $this->sponsorshipWorkflow;
        $toEnded = Action::new('to_ended')
            ->linkToCrudAction('toEnded')
            ->displayIf(static function (Sponsorship $sponsorship) use ($sw) {
                return $sw->can($sponsorship, 'to_ended');
            })
        ;

        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_INDEX, $toEnded)
            ->remove(Crud::PAGE_INDEX, Action::DELETE)
            ->remove(Crud::PAGE_DETAIL, Action::DELETE)
            ->remove(Crud::PAGE_INDEX, Action::EDIT)
        ;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(
                ChoiceFilter::new('status')
                    ->setChoices($this->sponsorshipWorkflow->getDefinition()->getPlaces())
                )
        ;
    }

    public function toEnded(AdminContext $adminContext)
    {
        $sponsorship = $adminContext->getEntity()->getInstance();
        $this->sponsorshipManager->validate($sponsorship, 'to_ended');

        return $this->redirect(
            $this->adminUrlGenerator
                ->setAction(Crud::PAGE_DETAIL)
                ->setController(SponsorshipCrudController::class)
                ->setEntityId($sponsorship->getId())
                ->generateUrl()
            );
    }
}
