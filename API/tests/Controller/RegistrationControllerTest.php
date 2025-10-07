<?php

namespace App\Tests\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class RegistrationControllerTest extends WebTestCase
{
    private $client;
    private $entityManager;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = $this->client->getContainer()->get('doctrine')->getManager();
        
        // Nettoyer la base de données avant chaque test
        $this->cleanDatabase();
        
        // Vider le cache d'entités pour éviter les conflits
        $this->entityManager->clear();
    }

    protected function tearDown(): void
    {
        // Nettoyer après chaque test
        if ($this->entityManager && $this->entityManager->getConnection()->isConnected()) {
            $this->cleanDatabase();
            $this->entityManager->clear();
            $this->entityManager->close();
        }
        
        $this->entityManager = null;
        $this->client = null;
        
        parent::tearDown();
    }

    private function cleanDatabase(): void
    {
        // Utiliser une requête SQL directe pour éviter les problèmes d'entités détachées
        $connection = $this->entityManager->getConnection();
        $databasePlatform = $connection->getDatabasePlatform()->getName();
        
        try {
            // Gestion spécifique selon le type de base de données
            if ($databasePlatform === 'mysql') {
                // MySQL
                $connection->executeStatement('SET FOREIGN_KEY_CHECKS = 0');
                $connection->executeStatement('DELETE FROM user');
                $connection->executeStatement('SET FOREIGN_KEY_CHECKS = 1');
                $connection->executeStatement('ALTER TABLE user AUTO_INCREMENT = 1');
            } elseif ($databasePlatform === 'sqlite') {
                // SQLite
                $connection->executeStatement('DELETE FROM user');
                $connection->executeStatement('DELETE FROM sqlite_sequence WHERE name = "user"');
            } else {
                // PostgreSQL et autres
                $connection->executeStatement('TRUNCATE TABLE user RESTART IDENTITY CASCADE');
            }
        } catch (\Exception $e) {
            // Fallback : méthode simple qui fonctionne partout
            $connection->executeStatement('DELETE FROM user');
        }
    }

    public function testSuccessfulRegistration(): void
    {
        $userData = [
            'email' => 'test@example.com',
            'password' => 'motdepasse123',
            'pseudo' => 'TestUser'
        ];

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
        $this->assertArrayHasKey('message', $responseData);
        $this->assertArrayHasKey('user_id', $responseData);
        $this->assertEquals('Utilisateur créé avec succès', $responseData['message']);
        $this->assertIsInt($responseData['user_id']);
        
        // Vérifier que l'utilisateur a les bonnes valeurs par défaut dès la réponse
        $user = $this->entityManager->getRepository(User::class)->find($responseData['user_id']);
        $this->assertEquals(10, $user->getXp(), 'L\'XP par défaut devrait être 10');
        $this->assertEquals('500.00', $user->getMoney(), 'L\'argent par défaut devrait être 500.00');
        $this->assertEquals(150, $user->getNrj(), 'L\'énergie par défaut devrait être 150');

        // Vérifier que l'utilisateur a bien été créé en base
        $user = $this->entityManager->getRepository(User::class)->find($responseData['user_id']);
        $this->assertNotNull($user);
        $this->assertEquals($userData['email'], $user->getEmail());
        $this->assertEquals($userData['pseudo'], $user->getPseudo());
        
        // Vérifier que le mot de passe a été hashé
        $this->assertNotEquals($userData['password'], $user->getPassword());
        $this->assertNotEmpty($user->getPassword());
        
        // Vérifier les valeurs par défaut ajoutées dans le contrôleur
        $this->assertEquals(10, $user->getXp(), 'L\'utilisateur devrait avoir 10 XP par défaut');
        $this->assertEquals('500.00', $user->getMoney(), 'L\'utilisateur devrait avoir 500.00 d\'argent par défaut');  
        $this->assertEquals(150, $user->getNrj(), 'L\'utilisateur devrait avoir 150 d\'énergie par défaut');
    }

    public function testMissingEmailField(): void
    {
        $userData = [
            'password' => 'motdepasse123',
            'pseudo' => 'TestUser'
        ];

        $this->client->request(
            'POST',
            '/api/register',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($userData)
        );

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('error', $responseData);
        $this->assertEquals('Email, password et pseudo sont requis', $responseData['error']);
    }

    public function testMissingPasswordField(): void
    {
        $userData = [
            'email' => 'test@example.com',
            'pseudo' => 'TestUser'
        ];

        $this->client->request(
            'POST',
            '/api/register',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($userData)
        );

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('error', $responseData);
        $this->assertEquals('Email, password et pseudo sont requis', $responseData['error']);
    }

    public function testMissingPseudoField(): void
    {
        $userData = [
            'email' => 'test@example.com',
            'password' => 'motdepasse123'
        ];

        $this->client->request(
            'POST',
            '/api/register',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($userData)
        );

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('error', $responseData);
        $this->assertEquals('Email, password et pseudo sont requis', $responseData['error']);
    }

    public function testMissingAllFields(): void
    {
        $userData = [];

        $this->client->request(
            'POST',
            '/api/register',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($userData)
        );

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('error', $responseData);
        $this->assertEquals('Email, password et pseudo sont requis', $responseData['error']);
    }

    public function testInvalidEmailFormat(): void
    {
        $userData = [
            'email' => 'email-invalide',
            'password' => 'motdepasse123',
            'pseudo' => 'TestUser'
        ];

        $this->client->request(
            'POST',
            '/api/register',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($userData)
        );

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('error', $responseData);
        $this->assertEquals('Erreurs de validation', $responseData['error']);
        $this->assertArrayHasKey('details', $responseData);
        $this->assertIsArray($responseData['details']);
    }

    public function testDuplicateEmail(): void
    {
        // Créer d'abord un utilisateur
        $existingUser = new User();
        $existingUser->setEmail('test@example.com');
        $existingUser->setPseudo('ExistingUser');
        $existingUser->setPassword('hashedpassword');
        
        $this->entityManager->persist($existingUser);
        $this->entityManager->flush();

        // Tenter de créer un autre utilisateur avec le même email
        $userData = [
            'email' => 'test@example.com',
            'password' => 'motdepasse123',
            'pseudo' => 'NewUser'
        ];

        $this->client->request(
            'POST',
            '/api/register',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($userData)
        );

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('error', $responseData);
        $this->assertEquals('Erreurs de validation', $responseData['error']);
        $this->assertArrayHasKey('details', $responseData);
        
        // Vérifier que le message d'erreur contient bien l'information sur l'email dupliqué
        $this->assertContains('Cet email est déjà utilisé.', $responseData['details']);
    }

    public function testEmptyStringFields(): void
    {
        $userData = [
            'email' => '',
            'password' => '',
            'pseudo' => ''
        ];

        $this->client->request(
            'POST',
            '/api/register',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($userData)
        );

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('error', $responseData);
        $this->assertEquals('Erreurs de validation', $responseData['error']);
        $this->assertArrayHasKey('details', $responseData);
        $this->assertIsArray($responseData['details']);
    }

    public function testInvalidJsonRequest(): void
    {
        $this->client->request(
            'POST',
            '/api/register',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            'invalid-json'
        );

        $response = $this->client->getResponse();
        // Le comportement peut varier selon la configuration Symfony
        // mais généralement cela devrait retourner une erreur 400 ou 500
        $this->assertContains($response->getStatusCode(), [
            Response::HTTP_BAD_REQUEST,
            Response::HTTP_INTERNAL_SERVER_ERROR
        ]);
    }

    public function testRegistrationWithSpecialCharacters(): void
    {
        $userData = [
            'email' => 'test+special@example.com',
            'password' => 'P@ssw0rd!123',
            'pseudo' => 'User_123-Test'
        ];

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
        $this->assertArrayHasKey('message', $responseData);
        $this->assertArrayHasKey('user_id', $responseData);
        $this->assertEquals('Utilisateur créé avec succès', $responseData['message']);
    }

    public function testRegistrationWithLongFields(): void
    {
        $userData = [
            'email' => str_repeat('a', 170) . '@example.com', // Email très long
            'password' => str_repeat('b', 100), // Mot de passe très long
            'pseudo' => str_repeat('c', 250) // Pseudo très long
        ];

        $this->client->request(
            'POST',
            '/api/register',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($userData)
        );

        $response = $this->client->getResponse();
        // Devrait échouer en raison des contraintes de longueur
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('error', $responseData);
        $this->assertEquals('Erreurs de validation', $responseData['error']);
    }
}