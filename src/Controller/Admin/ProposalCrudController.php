<?php

namespace App\Controller\Admin;

use App\Config\Gender;
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
                    return $sponsor->getLastname();
                })
            ,
            TextField::new('person.firstName')->hideOnForm(),
            TextField::new('person.lastName')->hideOnForm(),
            AssociationField::new('domains')
                ->setCrudController(DomainCrudController::class)->autocomplete()
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
