<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Entity\Images;
use App\Entity\Categorie;
use App\Entity\Utilisateur;
use App\Entity\Ville;
use App\Repository\EvenementsRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EvenementsRepository::class)]
class Evenements
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom_evenement = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description_event = null;

    #[ORM\Column]
    private ?\DateTime $date_creation = null;

    #[ORM\Column]
    private ?\DateTime $date_debut = null;

    #[ORM\Column]
    private ?\DateTime $date_fin = null;

    #[ORM\Column(length: 255)]
    private ?string $adresse = null;

    #[ORM\Column(length: 7)]
    private ?string $nbre_place = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 5, scale: 2)]
    private ?string $price_place = null;

    #[ORM\Column(length: 20, options: ['default' => 'pending'])]
    private ?string $status_validation = 'pending';

    #[ORM\Column(nullable: true)]
    private ?\DateTime $date_validation = null;


    #[ORM\Column(type: 'boolean')]

    private bool $is_Sponsor = false;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Categorie $Categorie = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Ville $Ville = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Utilisateur $Utilisateur = null;

    #[ORM\OneToMany(mappedBy: 'evenements', targetEntity: Images::class, cascade: ['persist', 'remove'])]
    private Collection $images;
    public function __construct()
    {
        $this->images = new ArrayCollection();
    }



    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNomEvenement(): ?string
    {
        return $this->nom_evenement;
    }

    public function setNomEvenement(string $nom_evenement): static
    {
        $this->nom_evenement = $nom_evenement;

        return $this;
    }

    public function getDescriptionEvent(): ?string
    {
        return $this->description_event;
    }

    public function setDescriptionEvent(string $description_event): static
    {
        $this->description_event = $description_event;

        return $this;
    }

    public function getDateCreation(): ?\DateTime
    {
        return $this->date_creation;
    }

    public function setDateCreation(\DateTime $date_creation): static
    {
        $this->date_creation = $date_creation;

        return $this;
    }

    public function getDateDebut(): ?\DateTime
    {
        return $this->date_debut;
    }

    public function setDateDebut(\DateTime $date_debut): static
    {
        $this->date_debut = $date_debut;

        return $this;
    }

    public function getDateFin(): ?\DateTime
    {
        return $this->date_fin;
    }

    public function setDateFin(\DateTime $date_fin): static
    {
        $this->date_fin = $date_fin;

        return $this;
    }

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(string $adresse): static
    {
        $this->adresse = $adresse;

        return $this;
    }

    public function getNbrePlace(): ?string
    {
        return $this->nbre_place;
    }

    public function setNbrePlace(string $nbre_place): static
    {
        $this->nbre_place = $nbre_place;

        return $this;
    }

    public function getPricePlace(): ?string
    {
        return $this->price_place;
    }

    public function setPricePlace(string $price_place): static
    {
        $this->price_place = $price_place;

        return $this;
    }

    public function getStatusValidation(): ?string
    {
        return $this->status_validation;
    }

    public function setStatusValidation(string $status_validation): static
    {
        $this->status_validation = $status_validation;

        return $this;
    }

    public function getDateValidation(): ?\DateTime
    {
        return $this->date_validation;
    }

    public function setDateValidation(?\DateTime $date_validation): static
    {
        $this->date_validation = $date_validation;

        return $this;
    }

  
    public function getIsSponsor() : bool
    {
        return $this->is_Sponsor;
    }

   
 
    public function setIsSponsor(bool $isSponsor): static
    {
        $this->is_Sponsor = $isSponsor;

        return $this;
    }


    public function getCategorie(): ?Categorie
    {
        return $this->Categorie;
    }

    public function setCategorie(?Categorie $Categorie): static
    {
        $this->Categorie = $Categorie;

        return $this;
    }

    public function getVille(): ?Ville
    {
        return $this->Ville;
    }

    public function setVille(?Ville $Ville): static
    {
        $this->Ville = $Ville;

        return $this;
    }

    public function getUtilisateur(): ?Utilisateur
    {
        return $this->Utilisateur;
    }

    public function setUtilisateur(?Utilisateur $Utilisateur): static
    {
        $this->Utilisateur = $Utilisateur;

        return $this;
    }
    public function getImages(): Collection
    {
        return $this->images;
    }
 
// Ajouter une image
    public function addImage(Images $image): self
    {
        if (!$this->images->contains($image)) {
            $this->images[] = $image;
            $image->setEvenements($this);
        }
        return $this;
    }

    // Retirer une image
    public function removeImage(Images $image): self
    {
        if ($this->images->removeElement($image)) {
            if ($image->getEvenements() === $this) {
                $image->setEvenements(null);
            }
        }
        return $this;
    }



 
}