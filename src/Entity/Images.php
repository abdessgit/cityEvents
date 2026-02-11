<?php

namespace App\Entity;

use App\Repository\ImagesRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ImagesRepository::class)]
class Images
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom_images = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Evenements $evenements = null;

    public function getId(): ?int
    {
        return $this->id;
    }
    
    public function getNomImages(): ?string
    {
        return $this->nom_images;
    }

    public function setNomImages(string $nom_images): static
    {
        $this->nom_images = $nom_images;

        return $this;
    }

    public function getEvenements(): ?Evenements
    {
        return $this->evenements;
    }

    public function setEvenements(?Evenements $evenements): static
    {
        $this->evenements = $evenements;

        return $this;
    }
}
