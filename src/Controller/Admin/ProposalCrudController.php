<?php

namespace App\Controller\Admin;

use App\Config\Gender;
use App\Config\Language;
use App\Config\Objective;
use App\Entity\Domain;
use App\Entity\Person;
use App\Entity\Proposal;
use App\Entity\Sponsor;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\LanguageType;
use Symfony\Component\Intl\Languages;

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
                ->hideOnIndex()
                ->setFormTypeOption('choice_label', function (Person $sponsor, $key, $value) {
                    return $sponsor->getFullname();
                })
            ,
            TextField::new('person.firstName')->hideOnForm(),
            TextField::new('person.lastName')->hideOnForm(),
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
