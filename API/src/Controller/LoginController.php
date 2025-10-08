<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class LoginController extends AbstractController
{
    #[Route('/api/login_check', name: 'api_login_check', methods: ['POST'])]
    public function loginCheck(): JsonResponse
    {
        // Cette méthode ne sera jamais appelée car elle est interceptée par le firewall
        // Elle existe seulement pour définir la route
        throw new \RuntimeException('This method should not be called directly.');
    }

    #[Route('/api/logout', name: 'api_logout', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function logout(): JsonResponse
    {
        // Avec JWT, la déconnexion côté serveur n'est pas nécessaire
        // Vous pouvez simplement informer le client que la déconnexion a réussi
        return $this->json(['message' => 'Déconnexion réussie'], 200);
    }
}
