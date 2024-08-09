<?php

namespace App\Controller\Admin;

use App\Config\Objective;
use App\Entity\Request;
use App\Entity\Sponsorship;
use App\Repository\SponsorshipRepository;
use App\Service\AccuracyCalculator;
use App\Service\SponsorshipManager;
use Doctrine\ORM\EntityManagerInterface;
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
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\LanguageType;
use Symfony\Component\Intl\Languages;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Workflow\WorkflowInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class RequestCrudController extends AbstractCrudController
{
    public function __construct(
        private AccuracyCalculator $accuracyCalculator,
        private SponsorshipRepository $sponsorshipRepository,
        private AdminUrlGenerator $adminUrlGenerator,
        private SponsorshipManager $sponsorshipManager,
        private TranslatorInterface $translator,
        private EntityManagerInterface $entityManager,
        #[Target('lead')]
        private WorkflowInterface $leadWorkflow,
    ) 
    {}


    public static function getEntityFqcn(): string
    {
        return Request::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            // ChoiceField::new('status')->setChoices($this->leadWorkflow->getDefinition()->getPlaces()),
            ChoiceField::new('status')
                ->setChoices($this->leadWorkflow->getDefinition()->getPlaces())
                ->hideOnForm()
            ,
            AssociationField::new('person')
                ->setCrudController(StudentCrudController::class)
            ,
            ChoiceField::new('language')
                ->setFormType(LanguageType::class)
                ->setTranslatableChoices(Languages::getNames())
                ->setFormTypeOption('multiple', true)
            ,
            ChoiceField::new('objective')
                ->setFormType(EnumType::class)
                ->setChoices(Objective::cases())
                ->setFormTypeOption('multiple', true)
            ,
            ArrayField::new('domains')
                ->onlyOnDetail()
                ->onlyOnIndex()
            ,
            AssociationField::new('domains')
                ->onlyOnForms()
                ->setCrudController(DomainCrudController::class)
                ->autocomplete()
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

        $nonSatisfiable = Action::new('not_satisfiable')
            ->linkToCrudAction('notSatisfiable')
            ->addCssClass('btn-warning')
            ->setLabel('Non satisfiable')
            ->displayIf(static function (Request $request) {
                return in_array($request->getStatus(), ['blocked']) && $request->getSponsorships()->count() === 0;
            })
        ;


        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_INDEX, $calculate)
            ->add(Crud::PAGE_DETAIL, $calculate)
            ->add(Crud::PAGE_INDEX, $nonSatisfiable)
            ->add(Crud::PAGE_DETAIL, $nonSatisfiable)
            ->remove(Crud::PAGE_INDEX, Action::NEW)
            ->update(Crud::PAGE_INDEX, Action::EDIT, function(Action $action){
                return $action->displayIf(static function (Request $request) {
                    return in_array($request->getStatus(), ['free']);
                });
            })
            ->remove(Crud::PAGE_INDEX, Action::DELETE)
            ->remove(Crud::PAGE_DETAIL, Action::DELETE)
        ;
    }
    
    public function configureFilters(Filters $filters): Filters
    {
        return $filters
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

    public function notSatisfiable(AdminContext $context)
    {
        $request = $context->getEntity()->getInstance();
        $this->leadWorkflow->apply($request, 'to_not_satisfiable');
        $this->entityManager->flush();
        return $this->redirect($this->adminUrlGenerator->setAction(Crud::PAGE_DETAIL)->setEntityId($request->getId())->generateUrl());
    }

    #[Route('/proposals/validate/{sponsorship}', name: 'app_admin_request_proposal_validate')]
    public function sponsorshipValidate(Sponsorship $sponsorship)
    {
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