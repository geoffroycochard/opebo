<?php

namespace App\Controller\Admin;

use App\Config\Civility;
use App\Entity\City;
use App\Entity\Course;
use App\Entity\Student;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Form\Extension\Core\Type\EnumType;

class StudentCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Student::class;
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
            TextField::new('course.sector.establishment')->hideOnForm(),
            TextField::new('course.sector')->hideOnForm(),
            TextField::new('course')->hideOnForm(),
            AssociationField::new('course')
                ->setFormTypeOption(
                    'choice_label', function(Course $course) {
                        return $course->getName();
                    }
                )
                ->hideOnIndex()
            ,
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
