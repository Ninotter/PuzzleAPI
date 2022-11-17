<?php

namespace App\Controller;

use App\Entity\LignePanier;
use App\Entity\User;
use App\Entity\Panier;
use App\Entity\Produit;
use App\Repository\PanierRepository;
use App\Repository\ProduitRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\Routing\Annotation\Route;
// use JMS\Serializer\Serializer;
// use JMS\Serializer\SerializerInterface;
// use JMS\Serializer\SerializerContext;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PanierController extends AbstractController
{
    /**
     * Ajoute un produit au panier actif d'un user avec la quantité spécifiée
     *
     * @param User $user
     * @param Produit $produit
     * @return JsonResponse
     */
    #[Route('/panier/ajouter', name: 'ajouterLignePanier',methods: ['POST'])]
    public function addToPanier(Request $request, PanierRepository $panierRepo, ProduitRepository $produitRepo, EntityManagerInterface $entityManager, SerializerInterface $serializer): JsonResponse
    {
        //Les données ne doivent pas avoir status 0
        $requestContent = $request->toArray();
        $produit = $produitRepo->find($requestContent["idProduit"]);
        $panier = $panierRepo->getUserActivePanier($requestContent["idUser"])[0];
        $quantity = $requestContent["quantity"];

        $lignePanierToAdd = new LignePanier();
        $lignePanierToAdd->setProduit($produit);
        $lignePanierToAdd->setPanier($panier);
        $lignePanierToAdd->setQuantity($quantity);

        $lignesPanierList = $panier->getLignesPanier();
        $lignesPanierList->add($lignePanierToAdd);
        $entityManager->persist($lignePanierToAdd);
        $entityManager->flush();
        $context = SerializationContext::create()->setGroups(['getLignePanier']);
        $lignePanierJson = $serializer->serialize($lignePanierToAdd, 'json', $context);
        return new JsonResponse($lignePanierJson, Response::HTTP_CREATED, [], true);
    }

    /**
     * Retire un produit au panier actif d'un user avec la quantité spécifiée
     *
     * @return JsonResponse
     */
    #[Route('/panier/supprimer', name: 'supprimerLignePanier',methods: ['POST'])]
    public function removeFromPanier(Request $request, PanierRepository $panierRepo, ProduitRepository $produitRepo, EntityManagerInterface $entityManager, SerializerInterface $serializer): JsonResponse
    {
        //Les données ne doivent pas avoir status 0
        $requestContent = $request->toArray();
        $produit = $produitRepo->find($requestContent["idProduit"]);
        $panier = $panierRepo->getUserActivePanier($requestContent["idUser"])[0];
        $quantity = $requestContent["quantity"];

        $lignesPanierList = $panier->getLignesPanier();
        $lp = null;
        $index = null;
        foreach ($lignesPanierList as $key => $value) {
            if($value->getProduit() == $produit){
                $lp = $value;
                $index = $key;
            }
        }
        if ($lp == null) {
            return new JsonResponse(array(), Response::HTTP_NOT_FOUND, [], false);
        }
        if (($lp->getQuantity() - $quantity) < 1) {
            $lignesPanierList->removeElement($lp);
            $entityManager->persist($panier);
            $entityManager->flush();
        }else {
            $lp->setQuantity($lp->getQuantity() - $quantity);
            $lignesPanierList->remove($index);
            $lignesPanierList->add($lp);
        }
        $context = SerializationContext::create()->setGroups(['getLignePanier']);
        $lignePanierJson = $serializer->serialize($lp, 'json', $context);
        return new JsonResponse($lignePanierJson, Response::HTTP_OK, [], true);
    }


}
