<?php

namespace App\Controller\Admin;

use App\Entity\Request;
use App\Entity\Sponsorship;
use App\Repository\SponsorshipRepository;
use App\Service\AccuracyCalculator;
use App\Service\SponsorshipManager;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Workflow\WorkflowInterface;

class RequestCrudController extends AbstractCrudController
{
    public function __construct(
        private AccuracyCalculator $accuracyCalculator,
        private SponsorshipRepository $sponsorshipRepository,
        private AdminUrlGenerator $adminUrlGenerator,
        private SponsorshipManager $sponsorshipManager,
        #[Target('lead')]
        private WorkflowInterface $leadWorkflow,
    ) {
    }


    public static function getEntityFqcn(): string
    {
        return Request::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id'),
            ChoiceField::new('status')->setChoices($this->leadWorkflow->getDefinition()->getPlaces()),
            AssociationField::new('person')->hideOnIndex(),
            TextField::new('person.firstName')->hideOnForm(),
            TextField::new('person.lastName')->hideOnForm(),
            ChoiceField::new('gender'),
            ChoiceField::new('language'),
            ChoiceField::new('objective'),
            CollectionField::new('domains')->renderExpanded()
        ];
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->overrideTemplate('crud/detail', 'admin/crud/request/detail.html.twig')
        ;
    }

    public function configureActions(Actions $actions): Actions
    {
        $calculate = Action::new('calculate')
            ->linkToCrudAction('calculate')
            ->displayIf(static function (Request $request) {
                return in_array($request->getStatus(), ['free', 'blocked']);
            })
        ;

        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_INDEX, $calculate)
            ->add(Crud::PAGE_DETAIL, $calculate)
            ->remove(Crud::PAGE_INDEX, Action::NEW)
            ->remove(Crud::PAGE_INDEX, Action::EDIT)
            ->remove(Crud::PAGE_INDEX, Action::DELETE)
            ->remove(Crud::PAGE_DETAIL, Action::EDIT)
            ->remove(Crud::PAGE_DETAIL, Action::DELETE)
        ;
    }
    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            // ->add('person')
            ->add(ChoiceFilter::new('status')->setChoices($this->leadWorkflow->getDefinition()->getPlaces()))
        ;
    }
    public function configureResponseParameters(KeyValueStore $responseParameters): KeyValueStore
    {
        if (Crud::PAGE_DETAIL === $responseParameters->get('pageName')) {
            $request = $responseParameters->get('entity')->getInstance();
            $sponsorphips = $this->sponsorshipRepository->findBy([
                'request' => $request->getId()
            ]);
            $responseParameters->set('sponsorships', $sponsorphips);
        }

        return $responseParameters;
    }

    public function calculate(AdminContext $context)
    {
        $request = $context->getEntity()->getInstance();
        $this->accuracyCalculator->calculate($request);
        return $this->redirect($this->adminUrlGenerator->setAction(Crud::PAGE_DETAIL)->setEntityId($request->getId())->generateUrl());
    }

    #[Route('/proposals/validate/{sponsorship}', name: 'app_admin_request_proposal_validate')]
    public function sponsorshipValidate(Sponsorship $sponsorship)
    {
        // dd('stop');
        $this->sponsorshipManager->adminProposal($sponsorship);

        return $this->redirect(
            $this->adminUrlGenerator
                ->setAction(Crud::PAGE_DETAIL)
                ->setController(SponsorshipCrudController::class)
                ->setEntityId($sponsorship->getId())
                ->generateUrl()
            );
    }
}