<?php

namespace App\Repository;

use App\Entity\Produit;
use DatePeriod;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Produit>
 *
 * @method Produit|null find($id, $lockMode = null, $lockVersion = null)
 * @method Produit|null findOneBy(array $criteria, array $orderBy = null)
 * @method Produit[]    findAll()
 * @method Produit[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProduitRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Produit::class);
    }

    public function save(Produit $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Produit $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function getDeletedProduits(): array{
        return $this->createQueryBuilder('p')
           ->andWhere('p.status = false')
           ->orderBy('p.id', 'ASC')
           ->getQuery()
           ->getResult()
       ;
    }

    public function getProduitsByDatePeriod(DatePeriod $dates): array{
        $dateDebut = DateTimeImmutable::createFromInterface($dates->getStartDate());
        $dateFin = DateTimeImmutable::createFromInterface($dates->getEndDate());
        return $this->createQueryBuilder('p')
           ->andWhere('p.status = true')
           ->andWhere('p.date_creation BETWEEN :dateDebut AND :dateFin')
           ->setParameter('dateDebut', $dateDebut)
           ->setParameter('dateFin', $dateFin)
           ->orderBy('date_creation.id', 'ASC')
           ->getQuery()
           ->getResult()
       ;
    }

    public function getAllProduitsFiltre(string $nom = "", string $prix = "", string $niveauDifficulte = "", string $nbPiece ="", string $tempsCompletion = "") : array{
        $nom = '%' . $nom . '%';
        $req = $this->createQueryBuilder('p')
           ->where('p.status = true')
           ->andWhere('p.nom LIKE :nomfiltre')
           ->setParameter('nomfiltre', $nom)
           ->orderBy('p.prix', $prix)
           ->addOrderBy('p.niveauDifficulte', $niveauDifficulte)
           ->addOrderBy('p.nbPiece', $nbPiece)
           ->addOrderBy('p.tempsCompletion', $tempsCompletion)
           ->getQuery()
       ;
    //    $sql = "SELECT * FROM Produit p WHERE p.status = true";
    //    $sql .= $nom ? " AND p.nom like :nom" :  "";
    //    $sql .= $prix ? " ORDER BY prix :prix" :  "";
    //    $sql .= $niveauDifficulte ? " order by niveau_difficulte :niveaudifficulte" :  " ";
    //    $sql .= $nbPiece ? " order by nb_piece :nbpiece " :  "";
    //    $sql .= $tempsCompletion ? " order by temps_completion :tempscompletion" :  "";
    //    $req = $this->getEntityManager()->createQuery($sql);
    //    if($nom) $req->setParameter(":nom", $nom);
    //    if($prix) $req->setParameter(":prix", $prix);
    //    if($niveauDifficulte) $req->setParameter(":niveaudifficulte", $niveauDifficulte);
    //    if($nbPiece) $req->setParameter("nbpiece" ,$nbPiece);
    //    if($tempsCompletion) $req->setParameter(":tempscompletion" ,$tempsCompletion);
       return $req->getResult();
    }

//    /**
//     * @return Produit[] Returns an array of Produit objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('p.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Produit
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
