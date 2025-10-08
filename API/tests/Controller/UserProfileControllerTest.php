<?php

namespace App\Tests\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserProfileControllerTest extends WebTestCase
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
        $connection = $this->entityManager->getConnection();
        
        try {
            $connection->executeStatement('DELETE FROM map');
            $connection->executeStatement('DELETE FROM user');
            $connection->executeStatement('ALTER TABLE user AUTO_INCREMENT = 1');
            $connection->executeStatement('ALTER TABLE map AUTO_INCREMENT = 1');
        } catch (\Exception $e) {
            // Ignorer les erreurs si les tables n'existent pas encore
        }
    }

    /**
     * Test pour nettoyer manuellement la base de données
     * Lancez ce test quand vous voulez repartir à zéro
     */
    public function testManualCleanDatabase(): void
    {
        $this->cleanDatabase();
        
        $userCount = $this->entityManager->getRepository(User::class)->count([]);
        $this->assertEquals(0, $userCount, 'La base de données devrait être vide après nettoyage');
        
        $this->addToAssertionCount(1); // Compter ce test comme réussi
    }

    private function createTestUser(): User
    {
        $uniqueId = uniqid();
        $user = new User();
        $user->setEmail("test_{$uniqueId}@example.com");
        $user->setPseudo("testuser_{$uniqueId}");
        $user->setRoles(['ROLE_USER']);
        $user->setXp(100);
        $user->setMoney('250.50');
        $user->setNrj(80);
        
        $hashedPassword = $this->passwordHasher->hashPassword($user, 'password123');
        $user->setPassword($hashedPassword);
        
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        
        return $user;
    }

    private function getAuthToken(string $email, string $password): string
    {
        $this->client->request('POST', '/api/login_check', [], [], 
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['username' => $email, 'password' => $password])
        );
        
        $response = json_decode($this->client->getResponse()->getContent(), true);
        return $response['token'];
    }

    public function testGetUserProfileSuccess(): void
    {
        // Créer un utilisateur test
        $user = $this->createTestUser();
        $token = $this->getAuthToken($user->getEmail(), 'password123');
        
        // Tester la récupération du profil
        $this->client->request('GET', '/api/user/profile', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
            'CONTENT_TYPE' => 'application/json'
        ]);
        
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        
        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        
        $this->assertArrayHasKey('id', $responseData);
        $this->assertArrayHasKey('pseudo', $responseData);
        $this->assertArrayHasKey('email', $responseData);
        $this->assertArrayHasKey('xp', $responseData);
        $this->assertArrayHasKey('money', $responseData);
        $this->assertArrayHasKey('nrj', $responseData);
        $this->assertArrayHasKey('roles', $responseData);
        
        $this->assertTrue(str_contains($responseData['pseudo'], 'testuser_'));
        $this->assertTrue(str_contains($responseData['email'], 'test_'));
        $this->assertEquals(100, $responseData['xp']);
        $this->assertEquals('250.50', $responseData['money']);
        $this->assertEquals(80, $responseData['nrj']);
        $this->assertContains('ROLE_USER', $responseData['roles']);
    }

    public function testGetUserProfileWithoutToken(): void
    {
        $this->client->request('GET', '/api/user/profile');
        
        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $this->client->getResponse()->getStatusCode());
    }

    public function testGetUserProfileWithInvalidToken(): void
    {
        $this->client->request('GET', '/api/user/profile', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer invalid_token_here',
            'CONTENT_TYPE' => 'application/json'
        ]);
        
        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $this->client->getResponse()->getStatusCode());
    }

    public function testGetUserProfileWithAdminRole(): void
    {
        // Créer un utilisateur admin
        $uniqueId = uniqid();
        $user = new User();
        $user->setEmail("admin_{$uniqueId}@example.com");
        $user->setPseudo("admin_{$uniqueId}");
        $user->setRoles(['ROLE_USER', 'ROLE_ADMIN']);
        $user->setXp(500);
        $user->setMoney('1000.00');
        $user->setNrj(150);
        
        $hashedPassword = $this->passwordHasher->hashPassword($user, 'admin123');
        $user->setPassword($hashedPassword);
        
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        
        $token = $this->getAuthToken($user->getEmail(), 'admin123');
        
        $this->client->request('GET', '/api/user/profile', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
            'CONTENT_TYPE' => 'application/json'
        ]);
        
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        
        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        
        $this->assertContains('ROLE_ADMIN', $responseData['roles']);
        $this->assertTrue(str_contains($responseData['pseudo'], 'admin_'));
    }
}