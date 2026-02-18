<?php

namespace App\Controller;

use App\Entity\Categorie;
use App\Repository\CategorieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class CategoryController extends AbstractController
{
  

     // Récupère ou crée une catégorie s'il n'existe pas
    public function getOrCreateCategory(EntityManagerInterface $entityManager, mixed $value, CategorieRepository $categories): ?Categorie
    {
        if ($value === null || $value === '') {
            return null;
        }

        // Si c'est un ID, cherche par ID
        if (is_numeric($value)) {
            return $categories->find((int) $value);
        }

        // Si c'est un nom, cherche par nom
        $category = $categories->findOneBy(['nom_categorie' => (string) $value]);
        
        // Si la catégorie n'existe pas, la créer
        if ($category === null) {
            $category = new Categorie();
            $category->setNomCategorie((string) $value);
            
            $entityManager->persist($category);
            $entityManager->flush();
        }

        return $category;
    }
    // Cherche l'ID d'une catégorie soit par son ID soit par son nom
    public function resolveCategoryId(mixed $value, CategorieRepository $categories): ?int
    {
        // Trouve l'ID catégorie depuis ID ou nom
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            return (int) $value;
        }

        $category = $categories->findOneBy(['nom_categorie' => (string) $value]);

        return $category?->getId();
    }
}