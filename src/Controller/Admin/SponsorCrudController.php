<?php

namespace App\Controller\Admin;

use App\Entity\City;
use App\Entity\Sponsor;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Form\Extension\Core\Type\EnumType;

class SponsorCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Sponsor::class;
    }


    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            ChoiceField::new('state')
                ->setFormType(EnumType::class)
            ,
            ChoiceField::new('civility')
                ->setFormType(EnumType::class)
            ,
            ChoiceField::new('gender')
                ->setFormType(EnumType::class)
            ,
            TextField::new('firstName'),
            TextField::new('lastName'),
            TextField::new('phone'),
            EmailField::new('email'),
            AssociationField::new('city')
                ->setFormTypeOption(
                    'choice_label', function(City $city) {
                        return $city->getName();
                    }
                )
            ,
            CollectionField::new('leads')->hideOnForm()
        ];
    }
}
