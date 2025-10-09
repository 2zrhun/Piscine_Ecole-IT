<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Map;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RegistrationController extends AbstractController
{
    #[Route('/api/register', name: 'api_register', methods: ['POST'])]
    public function register(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        // Vérifier que les données requises sont présentes
        if (!isset($data['email']) || !isset($data['password']) || !isset($data['pseudo']) || !isset($data['mapName'])) {
            return new JsonResponse([
                'error' => 'Email, password, pseudo et mapName sont requis'
            ], 400);
        }


        // Créer un nouvel utilisateur
        $user = new User();
        $user->setEmail($data['email']);
        $user->setPseudo($data['pseudo']);
        $user->setRoles(['ROLE_USER']); // Rôle par défaut
        $user->setXp(10);
        $user->setMoney('500.00');
        $user->setNrj(150);

        // Hasher le mot de passe
        $hashedPassword = $passwordHasher->hashPassword($user, $data['password']);
        $user->setPassword($hashedPassword);

        // Valider l'utilisateur avant persistance
        $userErrors = $validator->validate($user);
        $allErrors = [];
        foreach ($userErrors as $error) {
            $allErrors[] = $error->getMessage();
        }
        if (count($allErrors) > 0) {
            return new JsonResponse([
                'error' => 'Erreurs de validation',
                'details' => $allErrors
            ], 400);
        }

        // Persister l'utilisateur
        $entityManager->persist($user);
        $entityManager->flush();

        // Créer la map pour l'utilisateur
        $defaultConfig = [
            'grid' => ['size' => 50, 'division' => 20],
            'elements' => []
        ];
        $map = new Map();
        $map->setName($data['mapName']);
        $map->setUser($user);
        $map->setConfig($defaultConfig);
        $entityManager->persist($map);
        $entityManager->flush();

        // Valider la map
        $mapErrors = $validator->validate($map);
        $mapErrorList = [];
        foreach ($mapErrors as $error) {
            $mapErrorList[] = $error->getMessage();
        }
        if (count($mapErrorList) > 0) {
            return new JsonResponse([
                'error' => 'Erreurs de validation map',
                'details' => $mapErrorList
            ], 400);
        }

        return new JsonResponse([
            'message' => 'Utilisateur et map créés avec succès',
            'user_id' => $user->getId(),
            'map_id' => $map->getId(),
            'map_name' => $map->getName()
        ], 201);
    }
}