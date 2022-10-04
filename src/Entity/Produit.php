<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\ProduitRepository;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ProduitRepository::class)]
class Produit
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["getAllProduit", "getProduit"])]
    #[Assert\Type(type: 'integer', message:"L'id doit être un entier positif supérieur à 0.")]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(["getAllProduit", "getProduit"])]
    #[Assert\NotNull(message:"Un produit doit avoir un intitulé")]
    #[Assert\Type(type: 'string', message:"Le nom du produit doit être une chaîne")]
    #[Assert\Length(min : 4, minMessage:"Un produit doit avoir au moins 4 caractères")]
    private ?string $nom = null;

    #[Groups(["getAllProduit", "getProduit"])]
    #[Assert\Type(type: 'float', message:"Le nom du produit doit être un chiffre flottant")]
    #[ORM\Column(nullable: true)]
    private ?float $prix = null;

    #[Groups(["getAllProduit", "getProduit"])]
    #[Assert\Type(type: 'integer', message:"Le niveau de difficulté doit être un entier")]
    #[ORM\Column(nullable: true)]
    private ?int $niveauDifficulte = null;

    #[ORM\ManyToOne(inversedBy: 'lesProduits')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Type $Type = null;

    #[Groups(["getAllProduit", "getProduit"])]
    #[ORM\Column(nullable: true)]
    #[Assert\Type(type: 'integer', message:"Le nombre de pièces doit être un entier")]
    private ?int $nbPiece = null;

    #[Groups(["getAllProduit", "getProduit"])]
    #[Assert\Type(type: 'integer', message:"Le temps de complétion doit être un entier")]
    #[ORM\Column(nullable: true)]
    private ?int $tempsCompletion = null;

    #[ORM\Column]
    #[Assert\Type(type: 'bool', message:"Le status doit être un boolean")]
    private ?bool $status = null;

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

    public function isStatus(): ?bool
    {
        return $this->status;
    }

    public function setStatus(bool $status): self
    {
        $this->status = $status;

        return $this;
    }
}
