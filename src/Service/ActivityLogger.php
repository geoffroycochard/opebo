<?php
declare(strict_types=1);

namespace App\Service;
use App\Entity\Activity;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Workflow\Event\Event;
use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\Persistence\Proxy;

final class ActivityLogger 
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private Security $security
    )
    {}

    public function setLog(
        string $type, 
        string $fqcn,
        int $entityId,
        string $title, 
        string $content,
        string $status = 'info'
    )
    {
        $user = [];
        if ($this->security->getUser()) {
            $user = [
                'email' => $this->security->getUser()->getEmail(),
                'role' => implode(';', $this->security->getUser()->getRoles()),
            ];
        };
        $log = (new Activity)
            ->setTitle($title)
            ->setType($type)
            ->setEntityId($entityId)
            ->setFqcn($fqcn)
            ->setContent($content)
            ->setDatetime(new \DateTime())
            ->setUser($user)
        ;
        $this->entityManager->persist($log);
        $this->entityManager->flush();
    }

    public function logTransition(Event $event) 
    {
        $this->setLog(
            'workflow', 
            $this->getRealClass($event->getSubject()), 
            $event->getSubject()->getId(),
            'Transition',
            sprintf(
                '(id: "%s") performed transition "%s" from "%s" to "%s"',
                $event->getSubject()->getId(),
                $event->getTransition()->getName(),
                implode(', ', array_keys($event->getMarking()->getPlaces())),
                implode(', ', $event->getTransition()->getTos())
            ),
            'info'
        );
    }

    public function logEmailSuccess($object, string $title, string $content)
    {
        $this->setLog(
            'email', 
            $this->getRealClass($object), 
            $object->getId(),
            $title,
            $content,
            'info'
        );
    }

    public function logEmailFailed($object, string $title, string $content)
    {
        $this->setLog(
            'email', 
            $this->getRealClass($object), 
            $object->getId(),
            $title,
            $content,
            'error'
        );
    }

    private function getRealClass($object): string
    {
        if ($object instanceof Proxy) {
            return get_parent_class($object);
        }
        
        return get_class($object);
    }
}


