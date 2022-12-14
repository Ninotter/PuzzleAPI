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

    /**
     * Renvoie tous les produits actifs avec les filtres en param??tre
     *
     * @param string $nom
     * @param string $prix
     * @param string $niveauDifficulte
     * @param string $nbPiece
     * @param string $tempsCompletion
     * @return array
     */
    public function getAllProduitsFiltre(string $nom = "", string $prix = "", string $niveauDifficulte = "", string $nbPiece ="", string $tempsCompletion = "") : array{
        $req = $this->createQueryBuilder('p')
           ->where('p.status = true');
        if ($nom) {
        $req->andWhere('p.nom LIKE :nomfiltre')
        ->setParameter('nomfiltre', '%' . $nom . '%');
        }
        if(strtoupper($prix) == "ASC" || strtoupper($prix) == "DESC")$req->addOrderBy('p.prix', $prix);
        if(strtoupper($niveauDifficulte) == "ASC" || strtoupper($niveauDifficulte) == "DESC")$req->addOrderBy('p.niveauDifficulte', $niveauDifficulte);
        if(strtoupper($nbPiece) == "ASC" || strtoupper($nbPiece) == "DESC")$req->addOrderBy('p.nbPiece', $nbPiece);
        if(strtoupper($tempsCompletion) == "ASC" || strtoupper($tempsCompletion) == "DESC")$req->addOrderBy('p.tempsCompletion', $tempsCompletion);
        return $req->getQuery()->getResult();
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
