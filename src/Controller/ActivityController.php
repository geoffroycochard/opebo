<?php

namespace App\Controller;

use App\Repository\ActivityRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ActivityController extends AbstractController
{
    #[Route('/activity', name: 'app_activity')]
    public function list(string $fqcn, int $id, ActivityRepository $activityRepository): Response
    {

        $logs = $activityRepository->findBy([
            'fqcn' => $fqcn,
            'entityId' => $id
        ]);

        // dd($logs);

        return $this->render('activity/list.html.twig', [
            'logs' => $logs,
        ]);
    }
}
