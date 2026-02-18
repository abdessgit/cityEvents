<?php

namespace App\Controller;

use App\Entity\Ville;
use App\Repository\VilleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


final class VilleController extends AbstractController
{
    
     // Récupère ou crée une ville s'il n'existe pas
   public function getOrCreateCity( EntityManagerInterface $entityManager, mixed $value, VilleRepository $cities): ?Ville
    {
        if ($value === null || $value === '') {
            return null;
        }

        // Si c'est un nom, cherche par nom
        $city = $cities->findOneBy(['nom_ville' => (string) $value]);
        
        // Si la ville n'existe pas, la créer
        if ($city === null) {
            $city = new Ville();
            $city->setNomVille((string) $value);
            $city->setCodePostal('00000'); // Valeurs par défaut
            $city->setCodeInsee('000000');
            
            $entityManager->persist($city);
            $entityManager->flush();
        }

        return $city;
    }
        // Cherche l'ID d'une ville soit par son ID soit par son nom (pratique quand t'envoies le nom)
    public function resolveCityId(mixed $value, VilleRepository $cities): ?int
    {
        // Trouve l'ID ville depuis ID ou nom
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            return (int) $value;
        }

        $city = $cities->findOneBy(['nom_ville' => (string) $value]);

        return $city?->getId();
    }
}