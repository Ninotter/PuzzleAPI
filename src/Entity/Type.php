<?php

namespace App\Entity;

use App\Repository\TypeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TypeRepository::class)]
class Type
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\OneToMany(mappedBy: 'Type', targetEntity: Produit::class)]
    private Collection $lesProduits;

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
}
