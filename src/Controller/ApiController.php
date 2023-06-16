<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ApiController extends AbstractController
{
    #[Route('/api', name: 'app_api')]
    public function index(): ?Response
    {        
        $data = [
            'message' => 'Good to see you with correct api token'
        ];

        return new JsonResponse($data, Response::HTTP_OK);
    }

    #[Route('/api/language', name: 'app_api_language')]
    public function language(): ?Response
    {        
        $data = [
            'fr' => 'Français',
            'en' => 'Anglais',
            'cn' => 'Chinois'
        ];

        return new JsonResponse($data, Response::HTTP_OK);
    }
}
