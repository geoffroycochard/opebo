<?php

namespace App\Controller\Admin;

use App\Config\Civility;
use App\Entity\City;
use App\Entity\Course;
use App\Entity\Establishment;
use App\Entity\Student;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Intl\Countries;

class StudentCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Student::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->overrideTemplate('crud/detail', 'admin/crud/student/detail.html.twig')
        ;
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
            DateField::new('birthdate'),
            TextField::new('firstName'),
            TextField::new('lastName'),
            TextField::new('phone'),
            ChoiceField::new('nationality')
                ->setFormType(CountryType::class)
            ,
            AssociationField::new('establishment')
            ->setFormTypeOption(
                'choice_label', function(Establishment $establishment) {
                    return $establishment->getName();
                }
            ),
            EmailField::new('email'),
            AssociationField::new('city')
                ->setFormTypeOption(
                    'choice_label', function(City $city) {
                        return $city->getName();
                    }
                )
        ];
    }
}
