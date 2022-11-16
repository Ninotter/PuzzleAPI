<?php

namespace App\Entity;

use App\Repository\PanierRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PanierRepository::class)]
class Panier
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'paniers')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $User = null;

    #[ORM\Column]
    private ?bool $isComplete = null;

    #[ORM\Column]
    private ?bool $status = null;

    #[ORM\OneToMany(mappedBy: 'panier', targetEntity: Lignepanier::class, orphanRemoval: true)]
    private Collection $lignesPanier;

    public function __construct()
    {
        $this->produits = new ArrayCollection();
        $this->lignesPanier = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->User;
    }

    public function setUser(?User $User): self
    {
        $this->User = $User;

        return $this;
    }

    public function isIsComplete(): ?bool
    {
        return $this->isComplete;
    }

    public function setIsComplete(bool $isComplete): self
    {
        $this->isComplete = $isComplete;

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

    /**
     * @return Collection<int, Lignepanier>
     */
    public function getLignesPanier(): Collection
    {
        return $this->lignesPanier;
    }

    public function addLignesPanier(Lignepanier $lignesPanier): self
    {
        if (!$this->lignesPanier->contains($lignesPanier)) {
            $this->lignesPanier->add($lignesPanier);
            $lignesPanier->setPanier($this);
        }

        return $this;
    }

    public function removeLignesPanier(Lignepanier $lignesPanier): self
    {
        if ($this->lignesPanier->removeElement($lignesPanier)) {
            // set the owning side to null (unless already changed)
            if ($lignesPanier->getPanier() === $this) {
                $lignesPanier->setPanier(null);
            }
        }

        return $this;
    }
}
