<?php

namespace App\Controller\Admin;

use App\Entity\Building;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use Vich\UploaderBundle\Form\Type\VichImageType;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;

class BuildingCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Building::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('name'),
            Field::new('imageFile')
                ->setFormType(VichImageType::class)
                ->setFormTypeOptions([
                    'allow_delete' => false,
                    'download_uri' => false,
                    'attr' => [
                        'accept' => '.glb,.gltf,model/gltf-binary,application/octet-stream'
                    ]
                ])
                ->onlyOnForms(),
            ImageField::new('file')
                ->setBasePath('http://localhost:8000/uploads/building')
                ->setUploadDir('public/uploads/building')
                ->setUploadedFileNamePattern('[randomhash].[extension]')
                ->onlyOnIndex(),

            ImageField::new('image')
                ->setBasePath('http://localhost:8000/uploads/imageBuilding')
                ->setUploadDir('public/uploads/imageBuilding')
                ->setUploadedFileNamePattern('[randomhash].[extension]')
        ];
    }
}