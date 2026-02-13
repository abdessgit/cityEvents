<?php

namespace App\Controller;

use DateTime;
use App\Entity\Ticket;
use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class TicketController extends AbstractController
{
    // Route pour récupérer les tickets d'un utilisateur
    #[Route('/ticket/utilisateur/{id}', name: 'tickets_by_utilisateur', methods: ['GET'])]
    public function getTicketsByUtilisateur(int $id, EntityManagerInterface $entityManager): JsonResponse
    {
        $utilisateur = $entityManager->getRepository(Utilisateur::class)->find($id);
        if (!$utilisateur) {
            return $this->json(['error' => 'Utilisateur introuvable'], 404);
        }
        $tickets = $entityManager->getRepository(Ticket::class)->findByUtilisateur($utilisateur);
        $data = [];
        foreach ($tickets as $ticket) {
            $data[] = [
                'id' => $ticket->getId(),
                'object_ticket' => $ticket->getObjectTicket(),
                'message' => $ticket->getMessage(),
            ];
        }
        return $this->json($data);
    }

    // Route pour ajouter un ticket pour un utilisateur
    #[Route('/ticket/add', name: 'add_ticket', methods: ['POST'])]
    public function addTicket(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $utilisateurId = $data['utilisateur_id'] ?? null;
        $objectTicket = $data['object_ticket'] ?? null;
        $message = $data['message'] ?? null;

        if ($utilisateurId === "" || $objectTicket === "" || $message === "") {
            return $this->json(['error' => 'Champs manquants'], 400);
        }

        $utilisateur = $entityManager->getRepository(Utilisateur::class)->find($utilisateurId);
        if (!$utilisateur) {
            return $this->json(['error' => 'Utilisateur introuvable'], 404);
        }

        $ticket = new Ticket();
        $ticket->setObjectTicket($objectTicket);
        $ticket->setMessage($message);
        $ticket->setUtilisateur($utilisateur);

        $entityManager->persist($ticket);
        $entityManager->flush();

        return $this->json([
            'message' => 'Ticket ajouté avec succès',
            'ticket_id' => $ticket->getId()
        ], 201);
    }
}
