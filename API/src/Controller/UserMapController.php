<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\MapRepository;
// ...existing code...

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Http\Attribute\IsGranted;


final class UserMapController extends AbstractController
{
    #[Route('/api/user/map', name: 'api_user_map', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function getUserMap(MapRepository $mapRepository): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['error' => 'Utilisateur non authentifié'], 401);
        }
        $map = $mapRepository->findOneBy(['user' => $user]);
        if (!$map) {
            return new JsonResponse(['error' => 'Aucune map associée à cet utilisateur'], 404);
        }
        // Sérialisation automatique (si groupes bien configurés)
        return $this->json($map, 200, [], ['groups' => ['map:read']]);
    }
}
