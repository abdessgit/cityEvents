<?php

namespace App\Controller;

use App\Entity\Roles;
use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\UtilisateurRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use PDO;
use PDOException;

final class UtilisateurController extends AbstractController
{
    private function getPdoFromDatabaseUrl(): PDO
    {
        $databaseUrl = $_ENV['DATABASE_URL'] ?? $_SERVER['DATABASE_URL'] ?? '';
        if ($databaseUrl === '') {
            throw new \RuntimeException('DATABASE_URL introuvable');
        }

        $parts = parse_url($databaseUrl);
        if ($parts === false) {
            throw new \RuntimeException('DATABASE_URL invalide');
        }

        $host = $parts['host'] ?? '127.0.0.1';
        $port = $parts['port'] ?? 3306;
        $user = isset($parts['user']) ? urldecode($parts['user']) : 'root';
        $pass = isset($parts['pass']) ? urldecode($parts['pass']) : '';
        $dbName = isset($parts['path']) ? ltrim($parts['path'], '/') : '';

        $pdo = new PDO(
            "mysql:host={$host};port={$port};dbname={$dbName};charset=utf8mb4",
            $user,
            $pass,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]
        );

        return $pdo;
    }
   // ajouter un Utilisateur a la base donnes via postman
    #[Route('/api/v1/users/inscription', name: 'app_users_inscription', methods: ['POST'])]
    public function inscription(Request $request, EntityManagerInterface $manager , UserPasswordHasherInterface $passwordHasher): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $nom = isset($data['nom'])? $data['nom']: "";
        $prenom = isset($data['prenom'])? $data['prenom']: "";
        $email = isset($data['email'])? $data['email']: "";
        $mdp = isset($data['mdp'])? $data['mdp']: "";
        $tel = isset($data['tel'])? $data['tel']: "";
        $ip_adress = $request->getClientIp();


       if( $email === "" || $mdp === ""  || $nom  === "" || $prenom === "" || $tel === ""){
            return $this->json(['erreur' => "merci de remplire toutes les valeurs "], 400);
       }
           // Vérifier si l'email existe
        $emailExiste = $manager->getRepository(Utilisateur::class)
                                ->findOneBy(['email_utilisateur' => $email]);
        if ($emailExiste) {
            return $this->json(['erreur' => "Cet email est déjà utilisé"], 400);
        }
           // Vérifier si le téléphone existe 
        $telExiste = $manager->getRepository(Utilisateur::class)
                            ->findOneBy(['tel_utilisateur' => $tel]);
        if ($telExiste) {
            return $this->json(['erreur' => "Ce numéro de téléphone est déjà utilisé"], 400);
        }

        $roleEntity = $manager->getRepository(Roles::class)
                          ->findOneBy(['nom_role' => 'ROLE_USER']);

        if (!$roleEntity) {
            return $this->json(['erreur' => "Le rôle fourni n'existe pas"], 400);
        }
        $utilisateur = new Utilisateur();
        $utilisateur->setEmailUtilisateur($email);
        $utilisateur->setRoles($roleEntity);
        $hashedPassword = $passwordHasher->hashPassword(
            $utilisateur,
            $mdp
        );
        $utilisateur->setMdpUtilisateur($hashedPassword);
        $utilisateur->setNomUtilisateur($nom); 
        $utilisateur->setPrenomUtilisateur($prenom); 
        $utilisateur->setRoles($roleEntity);
        $utilisateur->setDateInscription(new \DateTime());
        $utilisateur->setTelUtilisateur($tel);
        $utilisateur->setIpUtilisateur($ip_adress);

        $manager->persist($utilisateur);
        $manager->flush();

    return $this->json([
        'success' => 'L\'utilisateur a bien été ajouté'
    ], 201);

    }

    // la methode pour ajouter un modeutilisateur$utilisateur ou Admin 
    #[Route('/api/v1/users/inscrire_moderator', name: 'app_inscrire_rator', methods: ['POST'])]
    public function inscrireModerator(Request $request, EntityManagerInterface $manager , UserPasswordHasherInterface $passwordHasher): JsonResponse
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            return $this->json(['erreur' => 'Acces refuse'], 403);
        }

        $data = json_decode($request->getContent(), true);

        $nom = isset($data['nom'])? $data['nom']: "";
        $prenom = isset($data['prenom'])? $data['prenom']: "";
        $email = isset($data['email'])? $data['email']: "";
        $mdp = isset($data['mdp'])? $data['mdp']: "";
        $tel = isset($data['tel'])? $data['tel']: "";
        $role = isset($data['role'])? $data['role']: "";
        $ip_adress = $request->getClientIp();

        if($nom === "" || $prenom === "" || $email === "" || $mdp === "" || $tel === ""){
             return $this->json(['erreur' => "merci de remplire la valeur "], 400);
        }

        $emailExiste = $manager->getRepository(Utilisateur::class)
                                ->findOneBy(['email_utilisateur' => $email]);
        if ($emailExiste) {
            return $this->json(['erreur' => "Cet email est déjà utilisé"], 400);
        }

        $telExiste = $manager->getRepository(Utilisateur::class)
                            ->findOneBy(['tel_utilisateur' => $tel]);
        if ($telExiste) {
            return $this->json(['erreur' => "Ce numéro de téléphone est déjà utilisé"], 400);
        }
        $roleEntity = $manager->getRepository(Roles::class)
                          ->findOneBy(['nom_role' => $role]);

        if (!$roleEntity) {
            return $this->json(['erreur' => "Le rôle fourni n'existe pas"], 400);
        }

        $utilisateur = new Utilisateur();
        $utilisateur->setEmailUtilisateur($email);
        $utilisateur->setRoles($roleEntity);
        $hashedPassword = $passwordHasher->hashPassword( $utilisateur,$mdp );

        $utilisateur->setMdpUtilisateur($hashedPassword);
        $utilisateur->setNomUtilisateur($nom); 
        $utilisateur->setPrenomUtilisateur($prenom); 
        $utilisateur->setRoles($roleEntity);
        $utilisateur->setDateInscription(new \DateTime());
        $utilisateur->setTelUtilisateur($tel);
        $utilisateur->setRoles($roleEntity);
        $utilisateur->setIpUtilisateur($ip_adress);

        $manager->persist($utilisateur);
        $manager->flush();

        return $this->json([
       'success' => 'un ' . $role . ' a bien été ajouté'
    ], 201);
    }

    #[Route('/api/v1/users/{id}/bloque', name: 'app_user_bloque', methods: ['PATCH'])]
    public function blockUser( int $id,  EntityManagerInterface $manager): JsonResponse
       
    {
    $user = $manager->getRepository(Utilisateur::class)->find($id);

    if (!$user) {
        return $this->json(['erreur' => 'Utilisateur introuvable'], 404);
    }

    $user->setBloquer(true);
    $manager->flush();

    return $this->json([
        'success' => 'Utilisateur bloqué avec succès'
    ]);
    }
    #[Route('/api/v1/users/{id}/debloquer', name: 'app_user_debloquer', methods: ['PATCH'])]
public function debloquerUser( int $id, EntityManagerInterface $manager): JsonResponse
   
{
    $user = $manager->getRepository(Utilisateur::class)->find($id);

    if (!$user) {
        return $this->json(['erreur' => 'Utilisateur introuvable'], 404);
    }
    $user->setBloquer(false);
    $manager->flush();

    return $this->json([
        'success' => 'Utilisateur débloqué avec succès'
    ]);
    }
    #[Route('/api/v1/users/connecter', name: 'app_user_connecter', methods: ['GET'])]
    public function connecter(): JsonResponse
    {
        $user = $this->getUser(); // récupère l'utilisateur authentifié via JWT

        if (!$user) {
            return $this->json(['erreur' => 'Utilisateur non connecté'], 401);
        }

        return $this->json([
            'userId' => $user->getId(),
            'email' => $user->getEmailUtilisateur(),
            'roles' => $user->getRoles(),
            'nom' => $user->getNomUtilisateur(),
            'prenom' => $user->getPrenomUtilisateur()
        ]);
    }

    //methode pour afficher tout les utilisateur 
    #[Route('/api/v1/users/get_user', name: 'app_get_user', methods: ['GET'])]
    public function getAllUser(EntityManagerInterface $manager, UtilisateurRepository $repo ): JsonResponse
    {

        $users = $repo->findAll();

        $filteredUsers = array_filter($users, function ($user) {
            return in_array('ROLE_USER', $user->getRoles());
        });

        $data = [];

        foreach ($filteredUsers as $user) {
            $data[] = [
                'id' => $user->getId(),
                'nom' => $user->getNomUtilisateur(),
                'prenom' => $user->getPrenomUtilisateur(),
                'email' => $user->getEmailUtilisateur(),
                'telephone' => $user->getTelUtilisateur(),
                'role' => $user->getRoles()[0] ?? 'ROLE_USER',
            ];
        }
         return $this->json($data);
    }
        
    #[Route('/api/v1/users/get_Mode', name: 'users_get_mode', methods: ['GET'])]
    public function getAllMode(UtilisateurRepository $repo): JsonResponse
    {
        $users = $repo->findAll();

        $filteredUsers = array_filter($users, function ($user) {
            return in_array('ROLE_MODERATEUR', $user->getRoles());
        });
        $data = [];
        foreach ($filteredUsers as $user) {
            $data[] = [
                'id' => $user->getId(),
                'nom' => $user->getNomUtilisateur(),
                'prenom' => $user->getPrenomUtilisateur(),
                'email' => $user->getEmailUtilisateur(),
                'telephone' => $user->getTelUtilisateur(),
                'role' => $user->getRoles()[0] ?? 'ROLE_MODERATEUR',
            ];
        }
        return $this->json($data);
    }

    #[Route('/api/v1/users/get_admin', name: 'users_get_admin', methods: ['GET'])]
    public function getAllAdmin(): JsonResponse
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            return $this->json(['erreur' => 'Acces refuse'], 403);
        }

        try {
            $pdo = $this->getPdoFromDatabaseUrl();
            $sql = "SELECT u.id, u.nom_utilisateur AS nom, u.prenom_utilisateur AS prenom, u.email_utilisateur AS email, u.tel_utilisateur AS telephone, r.nom_role AS role
                    FROM utilisateur u
                    INNER JOIN roles r ON r.id = u.roles_id
                    WHERE r.nom_role = 'ROLE_ADMIN'
                    ORDER BY u.id DESC";
            $stmt = $pdo->query($sql);
            $rows = $stmt->fetchAll();

            return $this->json($rows);
        } catch (PDOException $e) {
            return $this->json(['erreur' => 'Erreur lors du chargement des admins'], 500);
        }
    }

    #[Route('/api/v1/users/{id}/role', name: 'users_update_role', methods: ['PATCH'])]
    public function updateRole(int $id, Request $request): JsonResponse
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            return $this->json(['erreur' => 'Acces refuse'], 403);
        }

        $data = json_decode($request->getContent(), true) ?? [];
        $role = isset($data['role']) ? trim((string) $data['role']) : '';

        if ($role === '' || !in_array($role, ['ROLE_ADMIN', 'ROLE_MODERATEUR', 'ROLE_USER'])) {
            return $this->json(['erreur' => 'Role invalide'], 400);
        }

        try {
            $pdo = $this->getPdoFromDatabaseUrl();

            $stmtRole = $pdo->prepare("SELECT id FROM roles WHERE nom_role = :role LIMIT 1");
            $stmtRole->execute(['role' => $role]);
            $roleRow = $stmtRole->fetch();
            if (!$roleRow) {
                return $this->json(['erreur' => 'Role introuvable'], 404);
            }

            $stmtUser = $pdo->prepare("SELECT id, email_utilisateur FROM utilisateur WHERE id = :id LIMIT 1");
            $stmtUser->execute(['id' => $id]);
            $userRow = $stmtUser->fetch();
            if (!$userRow) {
                return $this->json(['erreur' => 'Utilisateur introuvable'], 404);
            }

            $stmtUpdate = $pdo->prepare("UPDATE utilisateur SET roles_id = :roleId WHERE id = :id");
            $stmtUpdate->execute([
                'roleId' => (int) $roleRow['id'],
                'id' => $id,
            ]);

            return $this->json([
                'success' => 'Role mis a jour avec succes',
                'user' => [
                    'id' => (int) $userRow['id'],
                    'email' => $userRow['email_utilisateur'],
                    'role' => $role,
                ],
            ]);
        } catch (PDOException $e) {
            return $this->json(['erreur' => 'Erreur lors de la mise a jour du role'], 500);
        }
    }

    #[Route('/api/v1/users/{id}', name: 'users_delete', methods: ['DELETE'])]
    public function deleteUser(int $id): JsonResponse
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            return $this->json(['erreur' => 'Acces refuse'], 403);
        }

        $currentUser = $this->getUser();
        if ($currentUser instanceof Utilisateur && $currentUser->getId() === $id) {
            return $this->json(['erreur' => 'Impossible de supprimer votre propre compte'], 400);
        }

        try {
            $pdo = $this->getPdoFromDatabaseUrl();

            $stmtUser = $pdo->prepare("SELECT id FROM utilisateur WHERE id = :id LIMIT 1");
            $stmtUser->execute(['id' => $id]);
            $userRow = $stmtUser->fetch();
            if (!$userRow) {
                return $this->json(['erreur' => 'Utilisateur introuvable'], 404);
            }

            $stmtDelete = $pdo->prepare("DELETE FROM utilisateur WHERE id = :id");
            $stmtDelete->execute(['id' => $id]);

            return $this->json(['success' => 'Utilisateur supprime avec succes']);
        } catch (PDOException $e) {
            return $this->json(['erreur' => 'Suppression impossible (utilisateur lie a d\'autres donnees)'], 400);
        }
    }

    #[Route('/api/v1/users/login', name: 'app_user_login', methods: ['POST'])]
    public function login(): JsonResponse
    {
        return $this->json(['success' => ' tester token']);
    }




}
    



