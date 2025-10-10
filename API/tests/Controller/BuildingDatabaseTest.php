<?php

namespace App\Tests\Controller;

use App\Entity\Building;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class BuildingDatabaseTest extends WebTestCase
{
    private $client;
    private $entityManager;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = $this->client->getContainer()->get('doctrine')->getManager();
        
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
        
        parent::tearDown();
    }

    private function cleanDatabase(): void
    {
        $connection = $this->entityManager->getConnection();
        
        try {
            $connection->executeStatement('DELETE FROM building');
            $connection->executeStatement('ALTER TABLE building AUTO_INCREMENT = 1');
        } catch (\Exception $e) {
            // Ignorer les erreurs si les tables n'existent pas encore
        }
    }

    public function testBuildingPersistenceInDatabase(): void
    {
        // Compter le nombre de buildings au départ
        $initialCount = $this->entityManager->getRepository(Building::class)->count([]);

        // Créer un nouveau building
        $building = new Building();
        $building->setName('Hôtel de Ville');
        $building->setFile('hotel_ville.jpg');
        $building->setImage('hotel_ville.jpg');

        // Persister en base
        $this->entityManager->persist($building);
        $this->entityManager->flush();
        
        // Vérifier que le building a été sauvegardé
        $this->assertNotNull($building->getId());
        
        // Vérifier le nombre total
        $finalCount = $this->entityManager->getRepository(Building::class)->count([]);
        $this->assertEquals($initialCount + 1, $finalCount);
        
        // Récupérer depuis la base pour vérifier
        $savedBuilding = $this->entityManager->getRepository(Building::class)->find($building->getId());
        
        $this->assertNotNull($savedBuilding);
        $this->assertEquals('Hôtel de Ville', $savedBuilding->getName());
        $this->assertEquals('hotel_ville.jpg', $savedBuilding->getFile());
    }

    public function testMultipleBuildingsPersistence(): void
    {
        $buildings = [
            ['name' => 'Centre Commercial', 'file' => 'centre_commercial.png'],
            ['name' => 'Bibliothèque', 'file' => 'bibliotheque.jpg'],
            ['name' => 'Parc', 'file' => 'parc.gif'],
        ];

        $initialCount = $this->entityManager->getRepository(Building::class)->count([]);

        // Créer et sauvegarder plusieurs buildings
        foreach ($buildings as $buildingData) {
            $building = new Building();
            $building->setName($buildingData['name']);
            $building->setFile($buildingData['file']);
            $building->setImage($buildingData['file']);

            $this->entityManager->persist($building);
        }
        
        $this->entityManager->flush();
        
        // Vérifier le nombre total
        $finalCount = $this->entityManager->getRepository(Building::class)->count([]);
        $this->assertEquals($initialCount + count($buildings), $finalCount);
        
        // Vérifier chaque building individuellement
        foreach ($buildings as $buildingData) {
            $savedBuilding = $this->entityManager->getRepository(Building::class)
                ->findOneBy(['name' => $buildingData['name']]);
            
            $this->assertNotNull($savedBuilding);
            $this->assertEquals($buildingData['name'], $savedBuilding->getName());
            $this->assertEquals($buildingData['file'], $savedBuilding->getFile());
        }
    }

    public function testBuildingUpdateInDatabase(): void
    {
        // Créer un building initial
        $building = new Building();
        $building->setName('Ancien Nom');
        $building->setFile('ancien_fichier.jpg');
        $building->setImage('ancien_fichier.jpg');

        $this->entityManager->persist($building);
        $this->entityManager->flush();
        
        $buildingId = $building->getId();
        
        // Modifier le building
        $building->setName('Nouveau Nom');
        $building->setFile('nouveau_fichier.png');
        
        $this->entityManager->flush();
        
        // Récupérer depuis la base pour vérifier les modifications
        $this->entityManager->clear(); // Clear l'EntityManager pour forcer la récupération
        $updatedBuilding = $this->entityManager->getRepository(Building::class)->find($buildingId);
        
        $this->assertNotNull($updatedBuilding);
        $this->assertEquals('Nouveau Nom', $updatedBuilding->getName());
        $this->assertEquals('nouveau_fichier.png', $updatedBuilding->getFile());
    }

    public function testBuildingDeletionFromDatabase(): void
    {
        // Créer un building
        $building = new Building();
        $building->setName('Building à supprimer');
        $building->setFile('delete_me.jpg');
        $building->setImage('delete_me.jpg');

        $this->entityManager->persist($building);
        $this->entityManager->flush();
        
        $buildingId = $building->getId();
        $initialCount = $this->entityManager->getRepository(Building::class)->count([]);
        
        // Supprimer le building
        $this->entityManager->remove($building);
        $this->entityManager->flush();
        
        // Vérifier que le building a été supprimé
        $deletedBuilding = $this->entityManager->getRepository(Building::class)->find($buildingId);
        $this->assertNull($deletedBuilding);
        
        // Vérifier le nombre total
        $finalCount = $this->entityManager->getRepository(Building::class)->count([]);
        $this->assertEquals($initialCount - 1, $finalCount);
    }

    public function testBuildingUniqueConstraints(): void
    {
        // Créer deux buildings avec des noms différents (pas de contrainte unique sur le nom pour l'instant)
        $building1 = new Building();
        $building1->setName('Building 1');
        $building1->setFile('building1.jpg');
        $building1->setImage('building1.jpg');

        $building2 = new Building();
        $building2->setName('Building 2');
        $building2->setFile('building2.jpg');
        $building2->setImage('building2.jpg');

        $this->entityManager->persist($building1);
        $this->entityManager->persist($building2);
        
        // Ceci devrait fonctionner sans problème
        $this->entityManager->flush();
        
        $this->assertNotNull($building1->getId());
        $this->assertNotNull($building2->getId());
        $this->assertNotEquals($building1->getId(), $building2->getId());
    }

    public function testBuildingDatabaseIntegrity(): void
    {
        // Test de l'intégrité des données après plusieurs opérations
        $building = new Building();
        $building->setName('Test Intégrité');
        $building->setFile('test_integrite.jpg');
        $building->setImage('test_integrite.jpg');

        $this->entityManager->persist($building);
        $this->entityManager->flush();
        
        // Vérifier que les données sont correctement typées
        $savedBuilding = $this->entityManager->getRepository(Building::class)->find($building->getId());
        
        $this->assertIsInt($savedBuilding->getId());
        $this->assertIsString($savedBuilding->getName());
        $this->assertIsString($savedBuilding->getFile());
        
        // Vérifier les longueurs maximales (selon votre schéma)
        $this->assertLessThanOrEqual(255, strlen($savedBuilding->getName()));
        $this->assertLessThanOrEqual(255, strlen($savedBuilding->getFile()));
    }
}