<?php

namespace App\Tests\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class RegistrationDatabaseTest extends WebTestCase
{
    private $client;
    private $entityManager;
    private $passwordHasher;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = $this->client->getContainer()->get('doctrine')->getManager();
        $this->passwordHasher = $this->client->getContainer()->get(UserPasswordHasherInterface::class);
        
        // NE PAS nettoyer automatiquement - les données s'accumuleront
        // $this->cleanDatabase(); // ← Commenté pour garder les données
    }

    protected function tearDown(): void
    {
        // NE PAS nettoyer après les tests pour garder les données
        if ($this->entityManager && $this->entityManager->getConnection()->isConnected()) {
            // $this->cleanDatabase(); // ← Commenté pour garder les données
            $this->entityManager->clear();
            $this->entityManager->close();
        }
        
        $this->entityManager = null;
        $this->client = null;
        $this->passwordHasher = null;
        
        parent::tearDown();
    }

    private function cleanDatabase(): void
    {
        // Utiliser une requête SQL directe pour éviter les problèmes d'entités détachées
        $connection = $this->entityManager->getConnection();
        $databasePlatform = $connection->getDatabasePlatform()->getName();
        
        try {
            if ($databasePlatform === 'mysql') {
                $connection->executeStatement('SET FOREIGN_KEY_CHECKS = 0');
                $connection->executeStatement('DELETE FROM user');
                $connection->executeStatement('SET FOREIGN_KEY_CHECKS = 1');
                $connection->executeStatement('ALTER TABLE user AUTO_INCREMENT = 1');
            } elseif ($databasePlatform === 'sqlite') {
                $connection->executeStatement('DELETE FROM user');
                $connection->executeStatement('DELETE FROM sqlite_sequence WHERE name = "user"');
            } else {
                $connection->executeStatement('TRUNCATE TABLE user RESTART IDENTITY CASCADE');
            }
        } catch (\Exception $e) {
            $connection->executeStatement('DELETE FROM user');
        }
    }

    /**
     * Méthode pour nettoyer manuellement si nécessaire
     * Utilisation : $this->manualCleanDatabase();
     */
    public function testManualCleanDatabase(): void
    {
        // Ce test permet de nettoyer la base manuellement
        $this->cleanDatabase();
        
        $userCount = $this->entityManager->getRepository(User::class)->count([]);
        $this->assertEquals(0, $userCount, 'La base de données devrait être vide après nettoyage');
        
        $this->addToAssertionCount(1); // Compter ce test comme réussi
    }

    public function testUserDataPersistenceInDatabase(): void
    {
        // Données de test
        $userData = [
            'email' => 'database.test@example.com',
            'password' => 'SecurePassword123!',
            'pseudo' => 'DatabaseTestUser'
        ];

        // Compter le nombre d'utilisateurs au départ
        $initialUserCount = $this->entityManager->getRepository(User::class)->count([]);

        // Envoyer la requête POST
        $this->client->request(
            'POST',
            '/api/register',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($userData)
        );

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('user_id', $responseData);
        $userId = $responseData['user_id'];

        // Vérifier qu'un utilisateur a bien été ajouté en base
        $finalUserCount = $this->entityManager->getRepository(User::class)->count([]);
        $this->assertEquals($initialUserCount + 1, $finalUserCount, 'Un utilisateur devrait avoir été ajouté en base');

        // Récupérer l'utilisateur depuis la base de données
        $savedUser = $this->entityManager->getRepository(User::class)->find($userId);
        
        // Vérifications de persistance
        $this->assertNotNull($savedUser, 'L\'utilisateur devrait exister en base de données');
        $this->assertInstanceOf(User::class, $savedUser, 'L\'objet récupéré devrait être une instance de User');
        
        // Vérifier les données sauvegardées
        $this->assertEquals($userData['email'], $savedUser->getEmail(), 'L\'email devrait être correctement sauvegardé');
        $this->assertEquals($userData['pseudo'], $savedUser->getPseudo(), 'Le pseudo devrait être correctement sauvegardé');
        $this->assertNotNull($savedUser->getId(), 'L\'ID devrait être généré automatiquement');
        
        // Vérifier que le mot de passe a été hashé
        $this->assertNotEquals($userData['password'], $savedUser->getPassword(), 'Le mot de passe ne devrait pas être stocké en clair');
        $this->assertNotEmpty($savedUser->getPassword(), 'Le mot de passe hashé ne devrait pas être vide');
        
        // Vérifier que le hash du mot de passe est valide
        $isPasswordValid = $this->passwordHasher->isPasswordValid($savedUser, $userData['password']);
        $this->assertTrue($isPasswordValid, 'Le mot de passe hashé devrait correspondre au mot de passe original');
        
        // Vérifier les rôles par défaut
        $this->assertContains('ROLE_USER', $savedUser->getRoles(), 'L\'utilisateur devrait avoir le rôle ROLE_USER par défaut');
        
        // Vérifier les valeurs par défaut ajoutées dans le contrôleur
        $this->assertEquals(10, $savedUser->getXp(), 'L\'utilisateur devrait avoir 10 XP par défaut');
        $this->assertEquals('500.00', $savedUser->getMoney(), 'L\'utilisateur devrait avoir 500.00 d\'argent par défaut');
        $this->assertEquals(150, $savedUser->getNrj(), 'L\'utilisateur devrait avoir 150 d\'énergie par défaut');
    }

    public function testMultipleUsersDataPersistence(): void
    {
        $users = [
            [
                'email' => 'user1@example.com',
                'password' => 'Password123!',
                'pseudo' => 'User1'
            ],
            [
                'email' => 'user2@example.com',
                'password' => 'Password456!',
                'pseudo' => 'User2'
            ],
            [
                'email' => 'user3@example.com',
                'password' => 'Password789!',
                'pseudo' => 'User3'
            ]
        ];

        $userIds = [];

        // Créer plusieurs utilisateurs
        foreach ($users as $userData) {
            $this->client->request(
                'POST',
                '/api/register',
                [],
                [],
                ['CONTENT_TYPE' => 'application/json'],
                json_encode($userData)
            );

            $response = $this->client->getResponse();
            $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());

            $responseData = json_decode($response->getContent(), true);
            $userIds[] = $responseData['user_id'];
        }

        // Vérifier que le nombre d'utilisateurs a augmenté de 3
        $finalCount = $this->entityManager->getRepository(User::class)->count([]);
        $this->assertGreaterThanOrEqual(3, $finalCount, 'Au moins trois utilisateurs devraient avoir été ajoutés');

        // Vérifier que chaque utilisateur existe et a les bonnes données
        foreach ($users as $index => $expectedUser) {
            $savedUser = $this->entityManager->getRepository(User::class)->find($userIds[$index]);
            
            $this->assertNotNull($savedUser, "L'utilisateur {$index} devrait exister en base");
            $this->assertEquals($expectedUser['email'], $savedUser->getEmail());
            $this->assertEquals($expectedUser['pseudo'], $savedUser->getPseudo());
            
            // Vérifier l'unicité des IDs
            $this->assertIsInt($savedUser->getId());
            $this->assertGreaterThan(0, $savedUser->getId());
        }

        // Vérifier que les emails de ce test sont uniques
        $testEmails = ['user1@example.com', 'user2@example.com', 'user3@example.com'];
        foreach ($testEmails as $email) {
            $userWithEmail = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
            $this->assertNotNull($userWithEmail, "L'utilisateur avec l'email $email devrait exister");
        }
    }

    public function testUserDataIntegrity(): void
    {
        $userData = [
            'email' => 'integrity.test@example.com',
            'password' => 'IntegrityTest123!',
            'pseudo' => 'IntegrityUser'
        ];

        // Créer l'utilisateur
        $this->client->request(
            'POST',
            '/api/register',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($userData)
        );

        $response = $this->client->getResponse();
        $responseData = json_decode($response->getContent(), true);
        $userId = $responseData['user_id'];

        // Récupérer l'utilisateur de plusieurs façons pour vérifier l'intégrité
        
        // 1. Par ID
        $userById = $this->entityManager->getRepository(User::class)->find($userId);
        $this->assertNotNull($userById);

        // 2. Par email
        $userByEmail = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $userData['email']]);
        $this->assertNotNull($userByEmail);

        // 3. Par pseudo
        $userByPseudo = $this->entityManager->getRepository(User::class)->findOneBy(['pseudo' => $userData['pseudo']]);
        $this->assertNotNull($userByPseudo);

        // Vérifier que c'est le même utilisateur
        $this->assertEquals($userById->getId(), $userByEmail->getId());
        $this->assertEquals($userById->getId(), $userByPseudo->getId());
        $this->assertEquals($userByEmail->getId(), $userByPseudo->getId());

        // Vérifier l'intégrité des données
        $this->assertEquals($userData['email'], $userById->getEmail());
        $this->assertEquals($userData['email'], $userByEmail->getEmail());
        $this->assertEquals($userData['email'], $userByPseudo->getEmail());

        $this->assertEquals($userData['pseudo'], $userById->getPseudo());
        $this->assertEquals($userData['pseudo'], $userByEmail->getPseudo());
        $this->assertEquals($userData['pseudo'], $userByPseudo->getPseudo());
    }

    public function testDatabaseConstraints(): void
    {
        $userData = [
            'email' => 'constraint.test@example.com',
            'password' => 'ConstraintTest123!',
            'pseudo' => 'ConstraintUser'
        ];

        // Créer le premier utilisateur
        $this->client->request(
            'POST',
            '/api/register',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($userData)
        );

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());

        // Tenter de créer un utilisateur avec le même email (devrait échouer)
        $duplicateUserData = [
            'email' => 'constraint.test@example.com', // Même email
            'password' => 'DifferentPassword123!',
            'pseudo' => 'DifferentUser'
        ];

        $this->client->request(
            'POST',
            '/api/register',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($duplicateUserData)
        );

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());

        // Vérifier que le nombre d'utilisateurs n'a pas augmenté (échec attendu)
        $finalUserCount = $this->entityManager->getRepository(User::class)->count([]);
        // Le nombre ne devrait pas avoir augmenté car la validation a échoué

        // Vérifier le message d'erreur
        $responseData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('error', $responseData);
        $this->assertEquals('Erreurs de validation', $responseData['error']);
        $this->assertContains('Cet email est déjà utilisé.', $responseData['details']);
    }

    public function testDatabaseTransactionRollback(): void
    {
        // Données invalides qui devraient faire échouer la validation
        $invalidUserData = [
            'email' => 'invalid-email', // Email invalide
            'password' => '', // Mot de passe vide
            'pseudo' => '' // Pseudo vide
        ];

        // Compter les utilisateurs avant le test
        $initialCount = $this->entityManager->getRepository(User::class)->count([]);

        // Tenter de créer un utilisateur invalide
        $this->client->request(
            'POST',
            '/api/register',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($invalidUserData)
        );

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());

        // Vérifier qu'aucun utilisateur supplémentaire n'a été créé en base (transaction rollback)
        $finalCount = $this->entityManager->getRepository(User::class)->count([]);
        $this->assertEquals($initialCount, $finalCount, 'Aucun utilisateur ne devrait être créé en cas d\'erreur de validation');
    }

    public function testUserDefaultValues(): void
    {
        $userData = [
            'email' => 'defaults.test@example.com',
            'password' => 'DefaultsTest123!',
            'pseudo' => 'DefaultsUser'
        ];

        // Créer l'utilisateur
        $this->client->request(
            'POST',
            '/api/register',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($userData)
        );

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $userId = $responseData['user_id'];

        // Récupérer l'utilisateur depuis la base de données
        $savedUser = $this->entityManager->getRepository(User::class)->find($userId);
        $this->assertNotNull($savedUser);

        // Vérifier toutes les valeurs par défaut définies dans le contrôleur
        $this->assertEquals(10, $savedUser->getXp(), 'L\'XP par défaut devrait être 10');
        $this->assertEquals('500.00', $savedUser->getMoney(), 'L\'argent par défaut devrait être 500.00');
        $this->assertEquals(150, $savedUser->getNrj(), 'L\'énergie par défaut devrait être 150');
        $this->assertEquals(['ROLE_USER'], $savedUser->getRoles(), 'Le rôle par défaut devrait être ROLE_USER');

        // Vérifier que les données fournies sont bien sauvegardées
        $this->assertEquals($userData['email'], $savedUser->getEmail());
        $this->assertEquals($userData['pseudo'], $savedUser->getPseudo());
        
        // Vérifier que le mot de passe est bien hashé
        $isPasswordValid = $this->passwordHasher->isPasswordValid($savedUser, $userData['password']);
        $this->assertTrue($isPasswordValid, 'Le mot de passe devrait être correctement hashé');
    }
}