<?php

namespace App\Entity;

use App\Repository\TypeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Serializer\Annotation\Groups;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: TypeRepository::class)]
class Type
{
    #[Groups(["getAllProduit", "getProduit" , "getTypes"])]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[Assert\Type(type: 'integer', message:"L'id doit être un entier positif supérieur à 0.")]
    #[ORM\Column]
    private ?int $id = null;

    #[Groups(["getAllProduit", "getProduit", "getTypes"])]
    #[Assert\Type(type: 'string', message:"Le nom doit être une chaine de caractères")]
    #[Assert\Length(min : 4, minMessage:"Un produit doit avoir au moins 4 caractères")]
    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\OneToMany(mappedBy: 'Type', targetEntity: Produit::class)]
    private Collection $lesProduits;

    #[ORM\Column]
    private ?bool $status = true;

    public function __construct()
    {
        $this->lesProduits = new ArrayCollection();
    }

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

    /**
     * @return Collection<int, Produit>
     */
    public function getLesProduits(): Collection
    {
        return $this->lesProduits;
    }

    public function addLesProduit(Produit $lesProduit): self
    {
        if (!$this->lesProduits->contains($lesProduit)) {
            $this->lesProduits->add($lesProduit);
            $lesProduit->setType($this);
        }

        return $this;
    }

    public function removeLesProduit(Produit $lesProduit): self
    {
        if ($this->lesProduits->removeElement($lesProduit)) {
            // set the owning side to null (unless already changed)
            if ($lesProduit->getType() === $this) {
                $lesProduit->setType(null);
            }
        }

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
