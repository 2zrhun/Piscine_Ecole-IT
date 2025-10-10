<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\MapRepository;
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

    #[Route('/api/map/by-pseudo/{pseudo}', name: 'api_map_by_pseudo', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function getMapByPseudo(string $pseudo, MapRepository $mapRepository): JsonResponse
    {
        // Trouver l'utilisateur par pseudo via la map (relation User <-> Map)
        $map = $mapRepository->createQueryBuilder('m')
            ->join('m.user', 'u')
            ->where('u.pseudo = :pseudo')
            ->setParameter('pseudo', $pseudo)
            ->getQuery()
            ->getOneOrNullResult();

        if (!$map) {
            return new JsonResponse([
                'error' => 'Aucune carte trouvée pour ce joueur',
                'pseudo' => $pseudo
            ], 404);
        }

        // Retourner la map avec les infos du propriétaire
        return $this->json([
            'map' => $map,
            'owner' => [
                'pseudo' => $map->getUser()->getPseudo(),
                'xp' => $map->getUser()->getXp()
            ]
        ], 200, [], ['groups' => ['map:read']]);
    }
}
