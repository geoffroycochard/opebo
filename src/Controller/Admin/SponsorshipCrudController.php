<?php

namespace App\Controller\Admin;

use App\Entity\Sponsorship;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\Workflow\WorkflowInterface;

class SponsorshipCrudController extends AbstractCrudController
{
    public function __construct(
        #[Target('sponsorship')]
        private WorkflowInterface $sponsorshipWorkflow,
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
        ;
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
            ->remove(Crud::PAGE_INDEX, Action::EDIT)
        ;
    }

    public function toEnded(AdminContext $adminContext)
    {

    }
}
