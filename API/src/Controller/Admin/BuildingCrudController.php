<?php

namespace App\Controller\Admin;

use App\Entity\Building;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;

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
            ImageField::new('file')
                ->setUploadDir('public/uploads/building')
                ->setBasePath('http://localhost:8000/uploads/building')
                ->setUploadedFileNamePattern('[randomhash].[extension]'),
        ];
    }

}
