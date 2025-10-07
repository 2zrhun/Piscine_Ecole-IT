<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class LoginController extends AbstractController
{
    #[Route('/api/login_check', name: 'api_login_check', methods: ['POST'])]
    public function loginCheck(): JsonResponse
    {
        // Cette méthode ne sera jamais appelée car elle est interceptée par le firewall
        // Elle existe seulement pour définir la route
        throw new \RuntimeException('This method should not be called directly.');
    }
}
