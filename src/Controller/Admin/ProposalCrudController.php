<?php

namespace App\Controller\Admin;

use App\Config\Objective;
use App\Entity\Person;
use App\Entity\Proposal;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\LanguageType;

class ProposalCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Proposal::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('status')->hideOnForm(),
            AssociationField::new('person')
                ->setCrudController(SponsorCrudController::class)
            ,
            ChoiceField::new('language')
                ->setFormType(LanguageType::class)
                ->setFormTypeOption('multiple', true)
            ,
            ChoiceField::new('objective')
                ->setFormType(EnumType::class)
                ->setChoices(Objective::cases())
                ->setFormTypeOption('multiple', true)
            ,
            AssociationField::new('domains')
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

    /*
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id'),
            TextField::new('title'),
            TextEditorField::new('description'),
        ];
    }
    */
}
