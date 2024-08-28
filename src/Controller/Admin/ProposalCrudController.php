<?php

namespace App\Controller\Admin;

use App\Config\Objective;
use App\Config\PersonStatus;
use App\Entity\Person;
use App\Entity\Proposal;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ArrayFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\LanguageType;
use Symfony\Component\Intl\Languages;
use Symfony\Component\Workflow\WorkflowInterface;

class ProposalCrudController extends AbstractCrudController
{
    public function __construct(
        #[Target('lead')]
        private WorkflowInterface $leadWorkflow,
    )
    {}

    public static function getEntityFqcn(): string
    {
        return Proposal::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            ChoiceField::new('status')
                ->setChoices($this->leadWorkflow->getDefinition()->getPlaces())
                ->hideOnForm()
            ,
            AssociationField::new('person')
                ->setCrudController(SponsorCrudController::class)
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

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->remove(Crud::PAGE_INDEX, Action::NEW)
            ->update(Crud::PAGE_INDEX, Action::EDIT, function(Action $action){
                return $action->displayIf(static function (Proposal $proposal) {
                    return in_array($proposal->getStatus(), ['free']);
                });
            })
            ->remove(Crud::PAGE_INDEX, Action::DELETE)
            ->remove(Crud::PAGE_DETAIL, Action::DELETE)
        ;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setSearchFields(['person.lastname', 'person.firstname', 'domains.name'])
        ;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(
                ChoiceFilter::new('status')
                    ->setChoices($this->leadWorkflow->getDefinition()->getPlaces())
                )
            ->add(
                ArrayFilter::new('objective')
                    ->setChoices(array_column(Objective::cases(), 'value', 'name'))
                )
            ->add('domains')
        ;
    }
}
