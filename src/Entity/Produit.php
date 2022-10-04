<?php

namespace App\Entity;

use App\Repository\ProduitRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ProduitRepository::class)]
class Produit
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["getAllProduit", "getProduit"])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(["getAllProduit", "getProduit"])]
    private ?string $nom = null;

    #[Groups(["getAllProduit", "getProduit"])]
    #[ORM\Column(nullable: true)]
    private ?float $prix = null;

    #[Groups(["getAllProduit", "getProduit"])]
    #[ORM\Column(nullable: true)]
    private ?int $niveauDifficulte = null;

    #[ORM\ManyToOne(inversedBy: 'lesProduits')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Type $Type = null;

    #[ORM\Column(nullable: true)]
    private ?int $nbPiece = null;

    #[ORM\Column(nullable: true)]
    private ?int $tempsCompletion = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPrix(): ?float
    {
        return $this->prix;
    }

    public function setPrix(?float $prix): self
    {
        $this->prix = $prix;

        return $this;
    }

    public function getNiveauDifficulte(): ?int
    {
        return $this->niveauDifficulte;
    }

    public function setNiveauDifficulte(?int $niveauDifficulte): self
    {
        $this->niveauDifficulte = $niveauDifficulte;

        return $this;
    }

    public function getType(): ?Type
    {
        return $this->Type;
    }

    public function setType(?Type $Type): self
    {
        $this->Type = $Type;

        return $this;
    }

    public function getNbPiece(): ?int
    {
        return $this->nbPiece;
    }

    public function setNbPiece(?int $nbPiece): self
    {
        $this->nbPiece = $nbPiece;

        return $this;
    }

    public function getTempsCompletion(): ?int
    {
        return $this->tempsCompletion;
    }

    public function setTempsCompletion(?int $tempsCompletion): self
    {
        $this->tempsCompletion = $tempsCompletion;

        return $this;
    }
}
