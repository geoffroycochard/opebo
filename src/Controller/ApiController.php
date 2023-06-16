<?php

namespace App\Controller;

use App\Config\Language;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Contracts\Translation\TranslatorInterface;

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

    #[Route(
        '/api/language/{locale}', 
        name: 'app_api_language', 
        requirements: ['local' => 'fr|en']
    )]
    public function language($locale, TranslatorInterface $translator): ?Response
    {        
        $data = [];
        foreach (Language::cases() as $language) {
            $data[$language->value] = $translator->trans(
                $language->title(),
                [],
                null,
                $locale 
            );
        }
        return new JsonResponse($data, Response::HTTP_OK);
    }
}
