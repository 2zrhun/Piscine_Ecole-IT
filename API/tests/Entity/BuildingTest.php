<?php

namespace App\Tests\Entity;

use App\Entity\Building;
use PHPUnit\Framework\TestCase;

class BuildingTest extends TestCase
{
    public function testBuildingCreation(): void
    {
        $building = new Building();
        
        $this->assertNull($building->getId());
        $this->assertNull($building->getName());
        $this->assertNull($building->getFile());
    }

    public function testBuildingSettersAndGetters(): void
    {
        $building = new Building();
        
        $building->setName('Hôtel de Ville');
        $building->setFile('hotel_ville.jpg');
        
        $this->assertEquals('Hôtel de Ville', $building->getName());
        $this->assertEquals('hotel_ville.jpg', $building->getFile());
    }

    public function testBuildingNameValidation(): void
    {
        $building = new Building();
        
        // Test avec un nom valide
        $building->setName('Centre Commercial');
        $this->assertEquals('Centre Commercial', $building->getName());
        
        // Test avec un nom vide (sera validé par Symfony Validator)
        $building->setName('');
        $this->assertEquals('', $building->getName());
    }

    public function testBuildingFileValidation(): void
    {
        $building = new Building();
        
        // Test avec différents types de fichiers
        $validFiles = ['image.jpg', 'photo.png', 'building.gif', 'test.jpeg'];
        
        foreach ($validFiles as $file) {
            $building->setFile($file);
            $this->assertEquals($file, $building->getFile());
        }
    }
}