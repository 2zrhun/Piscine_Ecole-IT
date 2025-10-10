<?php

namespace App\Controller;

use App\Repository\BuildingRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class BuildingGalleryController extends AbstractController
{
    #[Route('/gallery/buildings/images', name: 'gallery_buildings_images', methods: ['GET'])]
    public function getBuildingsImages(BuildingRepository $buildingRepository): JsonResponse
    {
        $buildings = $buildingRepository->findAll();
        $result = [];
        foreach ($buildings as $building) {
            $imageFile = $building->getImage(); // nom du fichier en BDD
            $imageUrl = $imageFile ? 'http://localhost:8000/uploads/imageBuilding/' . $imageFile : null;
            $result[] = [
                'id' => $building->getId(),
                'name' => $building->getName(),
                'image' => $imageUrl,
                'file' => $building->getFile(),
            ];
        }
        return new JsonResponse($result);
    }
}
