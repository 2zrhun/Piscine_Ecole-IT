<?php

namespace App\Controller;

use App\Entity\User;
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
        if (!isset($data['email']) || !isset($data['password']) || !isset($data['pseudo'])) {
            return new JsonResponse([
                'error' => 'Email, password et pseudo sont requis'
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

        // Valider l'utilisateur
        $errors = $validator->validate($user);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            return new JsonResponse([
                'error' => 'Erreurs de validation',
                'details' => $errorMessages
            ], 400);
        }

        try {
            // Sauvegarder en base de données
            $entityManager->persist($user);
            $entityManager->flush();

            return new JsonResponse([
                'message' => 'Utilisateur créé avec succès',
                'user_id' => $user->getId()
            ], 201);

        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'Erreur lors de la création de l\'utilisateur',
                'details' => $e->getMessage()
            ], 500);
        }
    }
}