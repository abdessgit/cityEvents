<?php

namespace App\Entity;

use App\Entity\Roles;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\UtilisateurRepository;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

#[ORM\Entity(repositoryClass: UtilisateurRepository::class)]
class Utilisateur implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 120)]
    private ?string $nom_utilisateur = null;

    #[ORM\Column(length: 40)]
    private ?string $prenom_utilisateur = null;

    #[ORM\Column]
    private ?\DateTime $date_inscription = null;

    #[ORM\Column(length: 120)]
    private ?string $email_utilisateur = null;

    #[ORM\Column(length: 20)]
    private ?string $tel_utilisateur = null;

    #[ORM\Column(length: 50)]
    private ?string $ip_utilisateur = null;

    #[ORM\Column(length: 255)]
    private ?string $mdp_utilisateur = null;

    #[ORM\Column(type: 'boolean')]
    private bool $Bloquer = false;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Roles $roles = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNomUtilisateur(): ?string
    {
        return $this->nom_utilisateur;
    }

    public function setNomUtilisateur(string $nom_utilisateur): static
    {
        $this->nom_utilisateur = $nom_utilisateur;

        return $this;
    }

    public function getPrenomUtilisateur(): ?string
    {
        return $this->prenom_utilisateur;
    }

    public function setPrenomUtilisateur(string $prenom_utilisateur): static
    {
        $this->prenom_utilisateur = $prenom_utilisateur;

        return $this;
    }

    public function getDateInscription(): ?\DateTime
    {
        return $this->date_inscription;
    }

    public function setDateInscription(\DateTime $date_inscription): static
    {
        $this->date_inscription = $date_inscription;

        return $this;
    }

    public function getEmailUtilisateur(): ?string
    {
        return $this->email_utilisateur;
    }

    public function setEmailUtilisateur(string $email_utilisateur): static
    {
        $this->email_utilisateur = $email_utilisateur;

        return $this;
    }

    public function getTelUtilisateur(): ?string
    {
        return $this->tel_utilisateur;
    }

    public function setTelUtilisateur(string $tel_utilisateur): static
    {
        $this->tel_utilisateur = $tel_utilisateur;

        return $this;
    }

    public function getIpUtilisateur(): ?string
    {
        return $this->ip_utilisateur;
    }

    public function setIpUtilisateur(string $ip_utilisateur): static
    {
        $this->ip_utilisateur = $ip_utilisateur;

        return $this;
    }

    public function getMdpUtilisateur(): ?string
    {
        return $this->mdp_utilisateur;
    }

    public function setMdpUtilisateur(string $mdp_utilisateur): static
    {
        $this->mdp_utilisateur = $mdp_utilisateur;

        return $this;
    }

   public function getRoles(): array
    {
        return [$this->roles ? $this->roles->getNomRole() : 'ROLE_USER'];
    }

    public function setRoles(?Roles $roles): self
    {
        $this->roles = $roles;

        return $this;
    }
    public function Bloquer(): bool
    {
        return $this->Bloquer;
    }

    public function setBloquer(bool $Bloquer): self
    {
        $this->Bloquer = $Bloquer;
        return $this;
    }

    public function getUserIdentifier(): string  { return (string) $this->email_utilisateur; }
    public function getPassword(): ?string { return $this->mdp_utilisateur; }
    public function getSalt(): ?string { return null; }
    public function eraseCredentials(): void { }
    public function getPasswordHasherName(): ?string  { return null; }
}
