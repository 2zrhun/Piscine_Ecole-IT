<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class UserProfileController extends AbstractController
{
    #[Route('/api/user/profile', name:'api_user_profile', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function getUserProfile(): JsonResponse
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        if (!$user) {
            return $this->json(['error' => 'Utilisateur non trouvé'], 404);
        }

        return $this->json([
            'id' => $user->getId(),
            'pseudo' => $user->getPseudo(),
            'email' => $user->getEmail(),
            'xp' => $user->getXp(),
            'money' => $user->getMoney(),
            'nrj' => $user->getNrj(),
            'roles' => $user->getRoles()
        ]);
    }
}