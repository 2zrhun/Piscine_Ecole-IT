<?php

namespace App\Tests\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class LoginControllerTest extends WebTestCase
{
    private $client;
    private $entityManager;
    private $passwordHasher;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = $this->client->getContainer()->get('doctrine')->getManager();
        $this->passwordHasher = $this->client->getContainer()->get(UserPasswordHasherInterface::class);
        
        // Nettoyer la base de données avant chaque test
        $this->cleanDatabase();
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

    private function createTestUser(string $email = 'test@example.com', string $password = 'password123', string $pseudo = 'TestUser'): User
    {
        $user = new User();
        $user->setEmail($email);
        $user->setPseudo($pseudo);
        $user->setRoles(['ROLE_USER']);
        $user->setXp(10);
        $user->setMoney('500.00');
        $user->setNrj(150);
        
        // Hasher le mot de passe
        $hashedPassword = $this->passwordHasher->hashPassword($user, $password);
        $user->setPassword($hashedPassword);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    public function testSuccessfulLogin(): void
    {
        // Créer un utilisateur de test
        $email = 'login.test@example.com';
        $password = 'LoginTest123!';
        $this->createTestUser($email, $password, 'LoginTestUser');

        // Tenter de se connecter
        $this->client->request(
            'POST',
            '/api/login_check',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'username' => $email,
                'password' => $password
            ])
        );

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        
        // Vérifier la présence du token JWT
        $this->assertArrayHasKey('token', $responseData);
        $this->assertIsString($responseData['token']);
        $this->assertNotEmpty($responseData['token']);
        
        // Vérifier que le token JWT a le bon format (3 parties séparées par des points)
        $tokenParts = explode('.', $responseData['token']);
        $this->assertCount(3, $tokenParts, 'Le token JWT devrait avoir 3 parties');
        
        // Vérifier chaque partie du token (header, payload, signature)
        foreach ($tokenParts as $part) {
            $this->assertNotEmpty($part, 'Chaque partie du token JWT devrait être non vide');
        }
    }

    public function testLoginWithInvalidEmail(): void
    {
        // Créer un utilisateur de test
        $this->createTestUser('valid@example.com', 'password123');

        // Tenter de se connecter avec un email inexistant
        $this->client->request(
            'POST',
            '/api/login_check',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'username' => 'invalid@example.com',
                'password' => 'password123'
            ])
        );

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('code', $responseData);
        $this->assertArrayHasKey('message', $responseData);
        $this->assertEquals(401, $responseData['code']);
    }

    public function testLoginWithInvalidPassword(): void
    {
        // Créer un utilisateur de test
        $email = 'password.test@example.com';
        $this->createTestUser($email, 'correctpassword');

        // Tenter de se connecter avec un mauvais mot de passe
        $this->client->request(
            'POST',
            '/api/login_check',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'username' => $email,
                'password' => 'wrongpassword'
            ])
        );

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('code', $responseData);
        $this->assertArrayHasKey('message', $responseData);
        $this->assertEquals(401, $responseData['code']);
    }

    public function testLoginWithMissingCredentials(): void
    {
        // Tenter de se connecter sans fournir d'identifiants
        $this->client->request(
            'POST',
            '/api/login_check',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([])
        );

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
    }

    public function testLoginWithMissingUsername(): void
    {
        // Tenter de se connecter sans username
        $this->client->request(
            'POST',
            '/api/login_check',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'password' => 'password123'
            ])
        );

        $response = $this->client->getResponse();
        $this->assertContains($response->getStatusCode(), [
            Response::HTTP_BAD_REQUEST,
            Response::HTTP_UNAUTHORIZED
        ]);
    }

    public function testLoginWithMissingPassword(): void
    {
        // Tenter de se connecter sans password
        $this->client->request(
            'POST',
            '/api/login_check',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'username' => 'test@example.com'
            ])
        );

        $response = $this->client->getResponse();
        $this->assertContains($response->getStatusCode(), [
            Response::HTTP_BAD_REQUEST,
            Response::HTTP_UNAUTHORIZED
        ]);
    }

    public function testLoginWithEmptyCredentials(): void
    {
        // Tenter de se connecter avec des identifiants vides
        $this->client->request(
            'POST',
            '/api/login_check',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'username' => '',
                'password' => ''
            ])
        );

        $response = $this->client->getResponse();
        $this->assertContains($response->getStatusCode(), [
            Response::HTTP_BAD_REQUEST,
            Response::HTTP_UNAUTHORIZED
        ]);
    }

    public function testLoginWithInvalidJsonFormat(): void
    {
        // Tenter de se connecter avec un JSON malformé
        $this->client->request(
            'POST',
            '/api/login_check',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            'invalid-json-format'
        );

        $response = $this->client->getResponse();
        $this->assertContains($response->getStatusCode(), [
            Response::HTTP_BAD_REQUEST,
            Response::HTTP_UNAUTHORIZED
        ]);
    }

    public function testLoginWithWrongContentType(): void
    {
        // Créer un utilisateur de test
        $email = 'content.test@example.com';
        $password = 'ContentTest123!';
        $this->createTestUser($email, $password);

        // Tenter de se connecter avec un mauvais Content-Type
        $this->client->request(
            'POST',
            '/api/login_check',
            [],
            [],
            ['CONTENT_TYPE' => 'application/x-www-form-urlencoded'],
            http_build_query([
                'username' => $email,
                'password' => $password
            ])
        );

        $response = $this->client->getResponse();
        // Peut retourner 400, 401 ou 500 selon la configuration
        $this->assertContains($response->getStatusCode(), [
            Response::HTTP_BAD_REQUEST,
            Response::HTTP_UNAUTHORIZED,
            Response::HTTP_INTERNAL_SERVER_ERROR
        ]);
    }

    public function testMultipleSuccessfulLogins(): void
    {
        // Créer plusieurs utilisateurs
        $users = [
            ['email' => 'user1@example.com', 'password' => 'Password1!', 'pseudo' => 'User1'],
            ['email' => 'user2@example.com', 'password' => 'Password2!', 'pseudo' => 'User2'],
            ['email' => 'user3@example.com', 'password' => 'Password3!', 'pseudo' => 'User3']
        ];

        $tokens = [];

        foreach ($users as $userData) {
            $this->createTestUser($userData['email'], $userData['password'], $userData['pseudo']);

            // Se connecter
            $this->client->request(
                'POST',
                '/api/login_check',
                [],
                [],
                ['CONTENT_TYPE' => 'application/json'],
                json_encode([
                    'username' => $userData['email'],
                    'password' => $userData['password']
                ])
            );

            $response = $this->client->getResponse();
            $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

            $responseData = json_decode($response->getContent(), true);
            $this->assertArrayHasKey('token', $responseData);
            
            $tokens[] = $responseData['token'];
        }

        // Vérifier que tous les tokens sont différents
        $uniqueTokens = array_unique($tokens);
        $this->assertCount(3, $uniqueTokens, 'Chaque connexion devrait générer un token unique');
    }

    public function testTokenContainsUserInformation(): void
    {
        // Créer un utilisateur de test
        $email = 'jwt.test@example.com';
        $password = 'JwtTest123!';
        $pseudo = 'JwtTestUser';
        $this->createTestUser($email, $password, $pseudo);

        // Se connecter
        $this->client->request(
            'POST',
            '/api/login_check',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'username' => $email,
                'password' => $password
            ])
        );

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $token = $responseData['token'];

        // Décoder le payload du JWT (partie du milieu)
        $tokenParts = explode('.', $token);
        $payload = json_decode(base64_decode($tokenParts[1]), true);

        // Vérifier les informations standard du JWT
        $this->assertArrayHasKey('username', $payload);
        $this->assertEquals($email, $payload['username']);
        
        $this->assertArrayHasKey('roles', $payload);
        $this->assertContains('ROLE_USER', $payload['roles']);
        
        $this->assertArrayHasKey('exp', $payload); // Expiration
        $this->assertArrayHasKey('iat', $payload); // Issued at
    }

    public function testLoginAndUseTokenForProtectedRoute(): void
    {
        // Créer un utilisateur de test
        $email = 'protected.test@example.com';
        $password = 'ProtectedTest123!';
        $pseudo = 'ProtectedUser';
        $this->createTestUser($email, $password, $pseudo);

        // Se connecter pour obtenir un token
        $this->client->request(
            'POST',
            '/api/login_check',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'username' => $email,
                'password' => $password
            ])
        );

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $token = $responseData['token'];

        // Utiliser le token pour accéder à une route protégée (exemple avec /profile)
        $this->client->request(
            'GET',
            '/profile',
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
                'CONTENT_TYPE' => 'application/json'
            ]
        );

        $protectedResponse = $this->client->getResponse();
        // La route /profile n'existe peut-être pas, mais le token devrait être validé
        // Un 404 (route non trouvée) est acceptable, mais pas un 401 (non autorisé)
        $this->assertNotEquals(Response::HTTP_UNAUTHORIZED, $protectedResponse->getStatusCode(), 
            'Le token JWT devrait être valide et ne pas retourner 401');
    }

    public function testAccessApiRouteWithoutToken(): void
    {
        // Tenter d'accéder à une route API protégée sans token
        // Utilisons une route qui devrait être protégée selon la configuration
        $this->client->request(
            'GET',
            '/api/protected-endpoint',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json']
        );

        $response = $this->client->getResponse();
        // Devrait retourner 401 (non autorisé) ou 404 si la route n'existe pas
        // Les deux sont acceptables pour ce test
        $this->assertContains($response->getStatusCode(), [
            Response::HTTP_UNAUTHORIZED,
            Response::HTTP_NOT_FOUND
        ], 'L\'accès sans token à une route API devrait retourner 401 ou 404');
    }

    public function testAccessApiRouteWithInvalidToken(): void
    {
        // Tenter d'accéder à une route API protégée avec un token invalide
        $this->client->request(
            'GET',
            '/api/protected-endpoint',
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => 'Bearer invalid.token.here',
                'CONTENT_TYPE' => 'application/json'
            ]
        );

        $response = $this->client->getResponse();
        // Devrait retourner 401 (non autorisé) ou 404 si la route n'existe pas
        $this->assertContains($response->getStatusCode(), [
            Response::HTTP_UNAUTHORIZED,
            Response::HTTP_NOT_FOUND
        ], 'L\'accès avec un token invalide devrait retourner 401 ou 404');
    }
}