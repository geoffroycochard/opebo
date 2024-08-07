<?php
declare(strict_types=1);

namespace App\Service;
use App\Entity\Activity;
use Doctrine\ORM\EntityManagerInterface;

final class ActivityLogger 
{
    public function __construct(
        private EntityManagerInterface $entityManager
    )
    {}

    public function setLog(
        string $type, 
        string $fqcn,
        int $entityId,
        string $title, 
        string $content
    )
    {
        $log = (new Activity)
            ->setTitle($title)
            ->setType($type)
            ->setEntityId($entityId)
            ->setFqcm($fqcn)
            ->setContent($content)
            ->setDatetime(new \DateTime())
        ;
        $this->entityManager->persist($log);
        $this->entityManager->flush();
    }
}


