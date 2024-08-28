<?php

namespace App\Controller\Admin;

use App\Entity\Domain;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\BatchActionDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;

class DomainCrudController extends AbstractCrudController
{
    public function __construct(
        private AdminUrlGenerator $adminUrlGenerator,
    )
    {}

    public static function getEntityFqcn(): string
    {
        return Domain::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->addBatchAction(
                Action::new('merge', 'Fusionner les mots')
                    ->linkToCrudAction('mergeKeywords')
                    ->addCssClass('btn btn-warning')
                    ->setIcon('fa-solid fa-code-merge')
                )
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('name'),
            AssociationField::new('leads')->onlyOnIndex()
        ];
    }

    public function mergeKeywords(BatchActionDto $batchActionDto)
    {
        $className = $batchActionDto->getEntityFqcn();

        /** @var EntityManagerInterface $em */
        $em = $this->container->get('doctrine')->getManagerForClass($className);
        $qb = $em->getRepository($className)->createQueryBuilder('d');
        $qb->where(
            $qb->expr()->in('d.id', $batchActionDto->getEntityIds())
        );

        $words = $results = $ids = [];
        $results = $qb->getQuery()->getResult();

        /**
         * @var Domain $main
         */
        $main = $results[0];
        /** @var Domain $domain */
        foreach ($results as $domain) {
            $words = array_merge($words, explode(' ', $domain->getName()));
            $ids[] = $domain->getId();
            if ($domain !== $main) {
                $em->remove($domain);
                foreach ($domain->getLeads() as $lead) {
                    $lead->removeDomain($domain);
                    $lead->addDomain($main);
                    $em->persist($lead);
                }
            }
        }
        $words = array_unique($words);
        $main->setName(implode(' ', $words));
        $em->persist($main);
        $em->flush();

        return $this->redirect(
            $this->adminUrlGenerator
                ->setAction(Crud::PAGE_EDIT)
                ->setController(DomainCrudController::class)
                ->setEntityId($main->getId())
                ->generateUrl()
        );
    }
}
