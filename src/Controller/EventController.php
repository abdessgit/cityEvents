<?php

namespace App\Controller;

use App\Controller\CategoryController;
use App\Controller\VilleController;

use App\Entity\Evenements;
use App\Entity\Utilisateur;
use App\Repository\CategorieRepository;
use App\Repository\EvenementsRepository;

use App\Repository\UtilisateurRepository;
use App\Repository\VilleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class EventController extends AbstractController
{
    public function __construct(private EntityManagerInterface $entityManager) {}
    // Crée un nouvel événement (faut que tu remplisses tous les champs obligatoires)
    #[Route('/api/events_add/{id}/event', name: 'api_events_create', methods: ['POST'])]
    public function create(int $id, EntityManagerInterface $manager, CategoryController $categoryController, VilleController $villeController, EntityManagerInterface $entityManager, Request $request, UtilisateurRepository $users, VilleRepository $cities, CategorieRepository $categories, ImagesController $imagesController): JsonResponse
    {

        $user = $manager->getRepository(Utilisateur::class)->find($id);
        // Creation d'un event
        $data = $request->request->all();
        
        $files = $request->files->get('images');
        if ($files && !is_array($files)) {
            $files = [$files];
        }


        $required = [
            'title',
            'description',
            'dateCreation',
            'address',
            'seats',
            'price',
            'dateStart',
            'dateEnd',
            'cityId',
            'categoryId',
        ];
       

        foreach ($required as $field) {
        if (!isset($data[$field]) || $data[$field] === '') {
        return new JsonResponse(['error' => "Le champ '$field' est requis"], 400);
        }
        }
        // Récupérer et valider les objets AVANT de créer l'événement
      

        // Récupérer ou créer la ville si elle n'existe pas
        $cityValue = $data['cityId'] ?? $data['city'] ?? null;
        $city = $villeController->getOrCreateCity($entityManager, $cityValue, $cities);
        if ($city === null) {
            return new JsonResponse(['error' => 'Erreur lors de la création de la ville'], JsonResponse::HTTP_BAD_REQUEST);
        }

        // Récupérer ou créer la catégorie si elle n'existe pas
        $categoryValue = $data['categoryId'] ?? $data['category'] ?? null;
        $category = $categoryController->getOrCreateCategory($entityManager, $categoryValue, $categories);
        if ($category === null) {
            return new JsonResponse(['error' => 'Erreur lors de la création de la catégorie'], JsonResponse::HTTP_BAD_REQUEST);
        }

        // Créer l'événement avec les objets réels, pas les IDs
        $event = new Evenements();
        $event->setNomEvenement($data['title']);
        $event->setDescriptionEvent($data['description']);
        $event->setAdresse($data['address']);
        $event->setNbrePlace($data['seats']);
        $event->setPricePlace($data['price']);
        $event->setDateCreation(new \DateTime($data['dateCreation']));
        $event->setDateDebut(new \DateTime($data['dateStart']));
        $event->setDateFin(new \DateTime($data['dateEnd']));
        $event->setUtilisateur($user);
        $event->setVille($city);
        $event->setCategorie($category);

       


        $this->entityManager->persist($event);
        $this->entityManager->flush();


        $uploadedImages = [];
        if ($files) {
            $result = $imagesController->addImages($event, $files, $entityManager);

            if (isset($result['error'])) {
                return new JsonResponse($result, 400); // arrête tout, pas de flush
            }

            $uploadedImages = $result; // images valides
        }
       
    return new JsonResponse([
        'message' => 'Événement créé avec succès',
        'event' => [
            'id' => $event->getId(),
            'title' => $event->getNomEvenement(),
        ],
        'images' => $uploadedImages
    ], 201);
    }


    #[Route('/api/events_list', name: 'api_events_list_simple', methods: ['GET'])]
    public function listSimple(EvenementsRepository $events): JsonResponse
        {
            // Liste simple des events sans filtres
            $items = $events->findAll();
            $data = [];
            foreach ($items as $event) {
                $data[] = [
                 
                    'nom_evenement' => $event->getNomEvenement(),
                    'description_event' => $event->getDescriptionEvent(),
                    'date_creation' => $event->getDateCreation()?->format('Y-m-d H:i:s'),
                    'date_debut' => $event->getDateDebut()?->format('Y-m-d H:i:s'),
                    'date_fin' => $event->getDateFin()?->format('Y-m-d H:i:s'),
                    'adresse' => $event->getAdresse(),
                    'nbre_place' => $event->getNbrePlace(),
                    'price_place' => $event->getPricePlace(),
                ];
            }
            return new JsonResponse($data);
        }

        #[Route('/api/get_events_by_filtre', name: 'api_events_list_by_filter', methods: ['POST'])]
        public function listByFilter(Request $request, EvenementsRepository $events, VilleRepository $cities, CategorieRepository $categories): JsonResponse
        {   
            $data = json_decode($request->getContent(), true);
            
            $categoryParam = isset($data['category']) ? trim((string) $data['category']) : null;
            $cityParam = isset($data['city']) ? trim((string) $data['city']) : null;
            $dateParam = isset($data['date']) ? trim((string) $data['date']) : null;

            // Vérifier qu'au moins un filtre est fourni
            if (empty($categoryParam) && empty($cityParam) && empty($dateParam)) {
                return new JsonResponse(
                    ['error' => 'Au moins un filtre (category, city, ou date) est requis'],
                    JsonResponse::HTTP_BAD_REQUEST
                );
            }

            // Résoudre l'ID de la catégorie
            $categoryId = null;
            if (!empty($categoryParam)) {
                if (is_numeric($categoryParam)) {
                    $category = $categories->find((int)$categoryParam);
                    $categoryId = $category ? $category->getId() : null;
                } else {
                    $category = $categories->findOneBy(['nom_categorie' => $categoryParam]);
                    $categoryId = $category ? $category->getId() : null;
                }
            }

            // Résoudre l'ID de la ville
            $cityId = null;
            if (!empty($cityParam)) {
                if (is_numeric($cityParam)) {
                    $city = $cities->find((int)$cityParam);
                    $cityId = $city ? $city->getId() : null;
                } else {
                    $city = $cities->findOneBy(['nom_ville' => $cityParam]);
                    $cityId = $city ? $city->getId() : null;
                }
            }

            // Valider et convertir la date
            $date = null;
            if (!empty($dateParam)) {
                try {
                    $date = new \DateTime($dateParam);
                } catch (\Exception $e) {
                    return new JsonResponse(
                        ['error' => 'Format de date invalide, attendu : Y-m-d'],
                        JsonResponse::HTTP_BAD_REQUEST
                    );
                }
            }

            // Utiliser findByFilters du repository (qui gère les filtres multiples)
            $filteredEvents = $events->findByFilters($cityId, $categoryId, $date);

            $responseData = [];
            foreach ($filteredEvents as $event) {
                $responseData[] = [
                    'nom_evenement' => $event->getNomEvenement(),
                    'description_event' => $event->getDescriptionEvent(),
                    'date_creation' => $event->getDateCreation()?->format('Y-m-d H:i:s'),
                    'date_debut' => $event->getDateDebut()?->format('Y-m-d H:i:s'),
                    'date_fin' => $event->getDateFin()?->format('Y-m-d H:i:s'),
                    'adresse' => $event->getAdresse(),
                    'nbre_place' => $event->getNbrePlace(),
                    'price_place' => $event->getPricePlace(),
                ];
            }
            
            return $this->json($responseData);
        }      
    
        //afficher les evenements en fonction de lutilisateur
        #[Route('/api/events_by_user/{id}', name: 'api_events_by_user', methods: ['GET'])]
        public function listByUser(int $id, EvenementsRepository $events , UtilisateurRepository $users, EntityManagerInterface $manager): JsonResponse
        {

            $userActuel = $manager->getRepository(Utilisateur::class)->find($id);
           
            if(!$userActuel) {
                return new JsonResponse(['error' => 'Utilisateur introuvable'], JsonResponse::HTTP_NOT_FOUND);
            }
            $role = $userActuel->getRoles() ?? null;
  
            if (in_array('ROLE_ADMIN', $role) || in_array('ROLE_MODERATEUR', $role)) {
                $items = $events->findAll();
            } if(in_array('ROLE_USER', $role)) {
                $items = $events->findBy(['Utilisateur' => $userActuel]);
             } else {
                return new JsonResponse(['error' => 'Rôle utilisateur non reconnu'], JsonResponse::HTTP_FORBIDDEN);
             }

            $data = [];
            foreach ($items as $event) {
                $data[] = [
                    'id' => $event->getId(),
                    'nom_evenement' => $event->getNomEvenement(),
                    'description_event' => $event->getDescriptionEvent(),
                    'date_creation' => $event->getDateCreation()?->format('Y-m-d H:i:s'),
                    'date_debut' => $event->getDateDebut()?->format('Y-m-d H:i:s'),
                    'date_fin' => $event->getDateFin()?->format('Y-m-d H:i:s'),
                    'adresse' => $event->getAdresse(),
                    'nbre_place' => $event->getNbrePlace(),
                    'price_place' => $event->getPricePlace(),
                ];
            }

            return new JsonResponse($data);

        }
    // Affiche les détails d'un événement spécifique en cherchant par son ID
    #[Route('/api/events/{id<\d+>}', name: 'api_events_show', methods: ['GET'])]
    public function show(?Evenements $event = null): JsonResponse
    {
        // Detail d'un event par ID
        if (!$event) {
            return new JsonResponse(['error' => 'Evenement introuvable'], JsonResponse::HTTP_NOT_FOUND);
        }

        return new JsonResponse($event);
    }

    // Te montre les événements qui sont en attente de validation (pas encore approuvés)
    #[Route('/api/events/pending', name: 'api_events_pending', methods: ['GET'])]
    public function pending(EvenementsRepository $events): JsonResponse
    {
        // Liste des events en attente
        $items = $events->findBy(['status_validation' => 'pending']);
        $data = [];
        foreach ($items as $event) {
            $data[] = [
                'id' => $event->getId(),
                'nom_evenement' => $event->getNomEvenement(),
                'description_event' => $event->getDescriptionEvent(),
                'date_creation' => $event->getDateCreation()?->format('Y-m-d H:i:s'),
                'date_debut' => $event->getDateDebut()?->format('Y-m-d H:i:s'),
                'date_fin' => $event->getDateFin()?->format('Y-m-d H:i:s'),
                'adresse' => $event->getAdresse(),
                'nbre_place' => $event->getNbrePlace(),
                'price_place' => $event->getPricePlace(),
                'status_validation' => $event->getStatusValidation(),
                'is_sponsor' => $event->getIsSponsor(),
            ];
        }

        return new JsonResponse($data);
    }

    // Valide un événement et le marque comme approuvé (t'es manager quoi)
    #[Route('/api/events/{id<\d+>}/validate', name: 'api_events_validate', methods: ['PATCH'])]
    public function validate(?Evenements $event = null): JsonResponse
    {
        // Valide un event
        if (!$event) {
            return new JsonResponse(['error' => 'Evenement introuvable'], JsonResponse::HTTP_NOT_FOUND);
        }

        $event->setStatusValidation('valide');
        $event->setDateValidation(new \DateTime());
       

        $this->entityManager->flush();

        return new JsonResponse(
            [
                'message' => 'Evenement valide avec succes',
                'event' => $event,
            ]
        );
    }

    // Refuse un événement et tu dois donner une raison (pourquoi tu le rejettes)
    #[Route('/api/events/{id<\d+>}/refuse', name: 'api_events_refuse', methods: ['PATCH'])]
    public function refuse(?Evenements $event, Request $request): JsonResponse
    {
        // Refuse un event avec motif
        if (!$event) {
            return new JsonResponse(['error' => 'Evenement introuvable'], JsonResponse::HTTP_NOT_FOUND);
        }
        $data = json_decode($request->getContent(), true);
       
        $event->setStatusValidation('refuse');
        $event->setDateValidation(new \DateTime());
       

        $this->entityManager->flush();

        return new JsonResponse(
            [
                'message' => 'Evenement refuse avec succes',
                'event' => $event,
            ]
        );
    }
    // pour afficher l'événement  sponsoriés

    #[Route('/api/events/{id}/sponsored', name: 'api_events_sponsored', methods: ['PATCH'])]

    public function sponsored(EntityManagerInterface $entityManager, int $id): JsonResponse
    {
        // Liste des events sponsorises
        $eventSponsor = $entityManager->getRepository(Evenements::class)->find($id);
        if (!$eventSponsor) {
            return new JsonResponse(['message' => 'Aucun événement sponsorisé trouvé'], JsonResponse::HTTP_NOT_FOUND);
        }


        $eventSponsor->setIsSponsor(true);
        $entityManager->flush();



        return $this->json(['message' => 'événements sponsorisés']);
    }

 #[Route('/api/events/{id}/noSponsored', name: 'api_events_noSponsored', methods: ['PATCH'])]
 
   public function noSponsored(EntityManagerInterface $entityManager, int $id): JsonResponse
    {
        // Liste des events sponsorises
        $eventSponsor = $entityManager->getRepository(Evenements::class)->find($id);
        if (!$eventSponsor) {
            return new JsonResponse(['message' => 'Aucun événement sponsorisé trouvé'], JsonResponse::HTTP_NOT_FOUND);
        }


        $eventSponsor->setIsSponsor(false);
        $entityManager->flush();



        return $this->json(['message' => 'événements rendu non sponsorisés']);
    }

 #[Route('/api/events/listEventsSponsored', name: 'api_events_listEventsSponsored', methods: ['GET'])]
    public function listEventsSponsored(EntityManagerInterface $entityManager,EvenementsRepository $evenementsRepository) : JsonResponse
    {
      $eventsSponsor = $entityManager->getRepository(Evenements::class)->findBy(['is_Sponsor' => true]);
        if (!$eventsSponsor) {
               return new JsonResponse(['message' => 'Aucun événement sponsorisé trouvé'], JsonResponse::HTTP_NOT_FOUND);
       
    }
      $data = [];
        foreach ($eventsSponsor as $event) {
            $data[] = [
               
                'nom_evenement' => $event->getNomEvenement(),
                'description_event' => $event->getDescriptionEvent(),
                'date_debut' => $event->getDateDebut()->format('Y-m-d H:i:s'),
                'date_fin' => $event->getDateFin()->format('Y-m-d H:i:s'),
                'adresse' => $event->getAdresse(),
                'nbre_place' => $event->getNbrePlace(),
                'price_place' => $event->getPricePlace(),
          
            ];
        }

   
     return $this->json($data);
        }



}