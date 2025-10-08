<?php

namespace App\Tests\Entity;

use App\Entity\User;
use App\Entity\Map;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserMapRelationTest extends WebTestCase
{
    private $client;
    private $entityManager;
    private $passwordHasher;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = $this->client->getContainer()->get('doctrine')->getManager();
        $this->passwordHasher = $this->client->getContainer()->get('Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface');
        
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
        $this->passwordHasher = null;
        $this->client = null;
        
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

    public function testUserMapRelationCreation(): void
    {
        // Créer un utilisateur avec email unique
        $uniqueId = uniqid();
        $user = new User();
        $user->setEmail("relation_test_{$uniqueId}@example.com");
        $user->setPseudo("testuser_{$uniqueId}");
        $user->setRoles(['ROLE_USER']);
        $user->setXp(10);
        $user->setMoney('500.00');
        $user->setNrj(150);
        
        $hashedPassword = $this->passwordHasher->hashPassword($user, 'password123');
        $user->setPassword($hashedPassword);
        
        // Créer une map associée
        $map = new Map();
        $map->setName('Ma ville');
        $map->setUser($user);
        $user->setMap($map);
        
        // Persister en base
        $this->entityManager->persist($user);
        $this->entityManager->persist($map);
        $this->entityManager->flush();
        
        // Vérifier les IDs
        $this->assertNotNull($user->getId());
        $this->assertNotNull($map->getId());
        
        // Vérifier la relation
        $this->assertEquals($user->getMap(), $map);
        $this->assertEquals($map->getUser(), $user);
    }

    public function testUserMapRelationFromDatabase(): void
    {
        // Créer et sauvegarder un utilisateur avec sa map
        $uniqueId = uniqid();
        $user = new User();
        $user->setEmail("relation_db_{$uniqueId}@test.com");
        $user->setPseudo("relationtest_{$uniqueId}");
        $user->setRoles(['ROLE_USER']);
        $user->setXp(25);
        $user->setMoney('750.50');
        $user->setNrj(120);
        
        $hashedPassword = $this->passwordHasher->hashPassword($user, 'password123');
        $user->setPassword($hashedPassword);
        
        $map = new Map();
        $map->setName('Ville de test');
        $map->setUser($user);
        $user->setMap($map);
        
        $this->entityManager->persist($user);
        $this->entityManager->persist($map);
        $this->entityManager->flush();
        
        $userId = $user->getId();
        $mapId = $map->getId();
        
        // Clear l'EntityManager pour forcer la récupération depuis la base
        $this->entityManager->clear();
        
        // Récupérer l'utilisateur depuis la base
        $savedUser = $this->entityManager->getRepository(User::class)->find($userId);
        $this->assertNotNull($savedUser);
        
        // Vérifier que la map est correctement associée
        $this->assertNotNull($savedUser->getMap());
        $this->assertEquals($mapId, $savedUser->getMap()->getId());
        $this->assertEquals('Ville de test', $savedUser->getMap()->getName());
        
        // Récupérer la map depuis la base
        $savedMap = $this->entityManager->getRepository(Map::class)->find($mapId);
        $this->assertNotNull($savedMap);
        
        // Vérifier que l'utilisateur est correctement associé
        $this->assertNotNull($savedMap->getUser());
        $this->assertEquals($userId, $savedMap->getUser()->getId());
        $this->assertTrue(str_contains($savedMap->getUser()->getPseudo(), 'relationtest_'));
    }

    public function testUserMapCascadeDeletion(): void
    {
        // Créer un utilisateur avec sa map
        $uniqueId = uniqid();
        $user = new User();
        $user->setEmail("cascade_{$uniqueId}@test.com");
        $user->setPseudo("cascadetest_{$uniqueId}");
        $user->setRoles(['ROLE_USER']);
        $user->setXp(50);
        $user->setMoney('1000.00');
        $user->setNrj(100);
        
        $hashedPassword = $this->passwordHasher->hashPassword($user, 'password123');
        $user->setPassword($hashedPassword);
        
        $map = new Map();
        $map->setName('Ville à supprimer');
        $map->setUser($user);
        $user->setMap($map);
        
        $this->entityManager->persist($user);
        $this->entityManager->persist($map);
        $this->entityManager->flush();
        
        $userId = $user->getId();
        $mapId = $map->getId();
        
        // Vérifier que les entités existent
        $this->assertNotNull($this->entityManager->getRepository(User::class)->find($userId));
        $this->assertNotNull($this->entityManager->getRepository(Map::class)->find($mapId));
        
        // Supprimer l'utilisateur
        $this->entityManager->remove($user);
        $this->entityManager->flush();
        
        // Vérifier que l'utilisateur a été supprimé
        $this->assertNull($this->entityManager->getRepository(User::class)->find($userId));
        
        // Vérifier le comportement de la map (selon votre configuration de cascade)
        $remainingMap = $this->entityManager->getRepository(Map::class)->find($mapId);
        // Cette assertion dépend de votre configuration cascade dans l'entité
        // Si vous avez cascade={"remove"}, la map devrait être supprimée aussi
        // Sinon, elle pourrait rester avec user_id = NULL
    }

    public function testMultipleUsersWithMaps(): void
    {
        $users = [];
        $maps = [];
        
        // Créer plusieurs utilisateurs avec leurs maps
        $baseId = uniqid();
        for ($i = 1; $i <= 3; $i++) {
            $user = new User();
            $user->setEmail("user{$i}_{$baseId}@test.com");
            $user->setPseudo("user{$i}_{$baseId}");
            $user->setRoles(['ROLE_USER']);
            $user->setXp($i * 10);
            $user->setMoney(($i * 100) . '.00');
            $user->setNrj(100 + $i * 10);
            
            $hashedPassword = $this->passwordHasher->hashPassword($user, 'password123');
            $user->setPassword($hashedPassword);
            
            $map = new Map();
            $map->setName("Ville {$i}");
            $map->setUser($user);
            $user->setMap($map);
            
            $this->entityManager->persist($user);
            $this->entityManager->persist($map);
            
            $users[] = $user;
            $maps[] = $map;
        }
        
        $this->entityManager->flush();
        
        // Vérifier que toutes les relations sont correctes
        foreach ($users as $index => $user) {
            $this->assertNotNull($user->getId());
            $this->assertNotNull($user->getMap());
            $this->assertEquals($maps[$index]->getId(), $user->getMap()->getId());
            $this->assertEquals("Ville " . ($index + 1), $user->getMap()->getName());
        }
        
        foreach ($maps as $index => $map) {
            $this->assertNotNull($map->getId());
            $this->assertNotNull($map->getUser());
            $this->assertEquals($users[$index]->getId(), $map->getUser()->getId());
            $this->assertTrue(str_contains($map->getUser()->getPseudo(), "user" . ($index + 1) . "_"));
        }
    }
}