<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\ProduitRepository;
// use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
// use Hateoas\Configuration\Annotation as Hateoas;
use Hateoas\Configuration\Annotation\Exclusion;
use Hateoas\Configuration\Annotation\Relation;
use Hateoas\Configuration\Annotation\Route;
use JMS\Serializer\Annotation\Groups;

/**
* @Relation(
*   "self",
*   href= @Route(
*       "produit.get",
*       parameters = {"idProduit" = "expr(object.getId())"}
*   ),
*   exclusion = @Exclusion(groups="getProduit")
* )
* @Relation(
*   "up",
*   href= @Route(
*       "produit.getAll"
*   ),
*   exclusion = @Exclusion(groups="getAllProduit")
* )
*/
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

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateCreation = null;

    #[Groups(["getAllProduit", "getProduit"])]
    #[Assert\Type(type: 'string', message:"Le codeCountry doit être une chaîne de caractères")]
    #[Assert\Regex(
        pattern: '/^A(BW|FG|GO|IA|L[AB]|ND|R[EGM]|SM|T[A
FG]|U[ST]|ZE)|B(DI|E[LNS]|FA|G[DR]|H[RS]|IH|L[MRZ]|MU|OL|
R[ABN]|TN|VT|WA)|C(A[FN]|CK|H[ELN]|IV|MR|O[DGKLM]|PV|RI|U
[BW]|XR|Y[MP]|ZE)|D(EU|JI|MA|NK|OM|ZA)|E(CU|GY|RI|S[HPT]|
TH)|F(IN|JI|LK|R[AO]|SM)|G(AB|BR|EO|GY|HA|I[BN]|LP|MB|N[B
Q]|R[CDL]|TM|U[FMY])|H(KG|MD|ND|RV|TI|UN)|I(DN|MN|ND|OT|R
[LNQ]|S[LR]|TA)|J(AM|EY|OR|PN)|K(AZ|EN|GZ|HM|IR|NA|OR|WT)
|L(AO|B[NRY]|CA|IE|KA|SO|TU|UX|VA)|M(A[CFR]|CO|D[AGV]|EX|
HL|KD|L[IT]|MR|N[EGP]|OZ|RT|SR|TQ|US|WI|Y[ST])|N(AM|CL|ER
|FK|GA|I[CU]|LD|OR|PL|RU|ZL)|OMN|P(A[KN]|CN|ER|HL|LW|NG|O
L|R[IKTY]|SE|YF)|QAT|R(EU|OU|US|WA)|S(AU|DN|EN|G[PS]|HN|J
M|L[BEV]|MR|OM|PM|RB|SD|TP|UR|V[KN]|W[EZ]|XM|Y[CR])|T(C[A
D]|GO|HA|JK|K[LM]|LS|ON|TO|U[NRV]|WN|ZA)|U(GA|KR|MI|RY|SA
|ZB)|V(AT|CT|EN|GB|IR|NM|UT)|W(LF|SM)|YEM|Z(AF|MB|WE)$/ix', 
        message: "Le countryCode doit être au format ISO 3166-1 alpha-3.")]
    #[ORM\Column(length: 5, nullable: true)]
    private ?string $paysOrigine = null;

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

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->dateCreation;
    }

    public function setDateCreation(?\DateTimeInterface $dateCreation): self
    {
        $this->dateCreation = $dateCreation;

        return $this;
    }

    public function getPaysOrigine(): ?string
    {
        return $this->paysOrigine;
    }

    public function setPaysOrigine(?string $paysOrigine): self
    {
        $this->paysOrigine = $paysOrigine;

        return $this;
    }
}
