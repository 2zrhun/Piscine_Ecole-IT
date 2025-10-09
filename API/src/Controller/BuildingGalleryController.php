<?php

namespace App\Controller;

use App\Repository\BuildingRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class BuildingGalleryController extends AbstractController
{
    /**
     * @Route("/api/buildings/images", name="api_buildings_images", methods={"GET"})
     */
    public function getBuildingsImages(BuildingRepository $buildingRepository): JsonResponse
    {
        $buildings = $buildingRepository->findAll();
        $result = [];
        foreach ($buildings as $building) {
            $result[] = [
                'id' => $building->getId(),
                'name' => $building->getName(),
                'image' => $building->getImage(), // à adapter selon ta propriété
                'file' => $building->getFile(),   // si tu veux aussi le fichier 3D
            ];
        }
        return new JsonResponse($result);
    }
}
