<?php

namespace App\Controller;

use App\Entity\LignePanier;
use App\Entity\User;
use App\Entity\Panier;
use App\Entity\Produit;
use App\Repository\LignePanierRepository;
use App\Repository\PanierRepository;
use App\Repository\ProduitRepository;
use App\Repository\UserRepository;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Lcobucci\JWT\Token\Plain;
use OpenApi\Annotations\RequestBody;
use OpenApi\Annotations\XmlContent;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use OpenApi\Attributes as OA;
use OpenApi\Attributes\JsonContent;

class PanierController extends AbstractController
{


    /**
     * Retourne le panier de l'id Panier fourni en paramètre
     *
     * @return JsonResponse
     */
    #[OA\Parameter(name: 'idPanier',in: 'query',required: true, description: 'id du Panier à chercher',schema: new OA\Schema(type: 'integer'))]
    #[Route('/panier/ajouter', name: 'ajouterLignePanier',methods: ['GET'])]
    public function getPanier(Request $request, LignePanierRepository $lignePanierRepo, PanierRepository $panierRepo, ProduitRepository $produitRepo, EntityManagerInterface $entityManager, SerializerInterface $serializer): JsonResponse
    {
        $panier = $produitRepo->find($request->get("idPanier"));
        $context = SerializationContext::create()->setGroups(['getPanier', 'getLignePanier', 'getProduit']);
        $panierJson = $serializer->serialize($panier, 'json', $context);
        return new JsonResponse($panierJson, Response::HTTP_CREATED, [], true);
    }


    /**
     * Ajoute un produit au panier actif d'un user avec la quantité spécifiée
     * NE FONCTIONNE PAS DEPUIS NELMIO. UNIQUEMENT TESTABLE AVEC POSTMAN
     *
     * @param User $user
     * @param Produit $produit
     * @return JsonResponse
     */
    #[OA\Parameter(name: 'idProduit',in: 'query',description: 'Produit(s) à ajouter au panier',schema: new OA\Schema(type: 'integer'))]
    #[OA\Parameter(name: 'idUser',in: 'query',description: 'Id de l\'user à qui ajouter le(s) produit(s)',schema: new OA\Schema(type: 'integer'))]
    #[OA\Parameter(name: 'quantity',in: 'query',description: 'Quantité de produit à ajouter',schema: new OA\Schema(type: 'integer'))]
    #[Route('/panier/ajouter', name: 'ajouterLignePanier',methods: ['POST'])]
    public function addToPanier(Request $request, LignePanierRepository $lignePanierRepo, PanierRepository $panierRepo, ProduitRepository $produitRepo, EntityManagerInterface $entityManager, SerializerInterface $serializer): JsonResponse
    {
        //Les données ne doivent pas avoir status 0
        $requestContent = $request->toArray();
        $produit = $produitRepo->find($requestContent["idProduit"]);
        $panier = $panierRepo->getUserActivePanier($requestContent["idUser"]);
        $quantity = $requestContent["quantity"];

        $lignePanierToAdd = $lignePanierRepo->findByPanierAndProduit($panier->getId(), $produit->getId());

        //Si le produit existe déjà dans le panier, ajoute la quantité spécifié
        if ($lignePanierToAdd != null) {
            $lignePanierToAdd->setQuantity($lignePanierToAdd->getQuantity() + $quantity);
            $entityManager->persist($lignePanierToAdd);
            $entityManager->flush();
        }
        else{
            $lignePanierToAdd = new LignePanier();
            $lignePanierToAdd->setProduit($produit);
            $lignePanierToAdd->setPanier($panier);
            $lignePanierToAdd->setQuantity($quantity);
    
            $lignesPanierList = $panier->getLignesPanier();
            $lignesPanierList->add($lignePanierToAdd);
            $entityManager->persist($lignePanierToAdd);
            $entityManager->flush();
        }
        $context = SerializationContext::create()->setGroups(['getLignePanier', 'getProduit']);
        $lignePanierJson = $serializer->serialize($lignePanierToAdd, 'json', $context);
        return new JsonResponse($lignePanierJson, Response::HTTP_CREATED, [], true);
    }

    /**
     * Retire un produit au panier actif d'un user avec la quantité spécifiée
     * NE FONCTIONNE PAS DEPUIS NELMIO. UNIQUEMENT TESTABLE AVEC POSTMAN
     * @return JsonResponse
     */
    #[Route('/panier/supprimer', name: 'supprimerLignePanier',methods: ['DELETE'])]
    #[OA\Parameter(name: 'idProduit',in: 'query',description: 'Produit(s) à retirer du panier',schema: new OA\Schema(type: 'integer'))]
    #[OA\Parameter(name: 'idUser',in: 'query',description: 'Id de l\'user à qui retirer le(s) produit(s)',schema: new OA\Schema(type: 'integer'))]
    #[OA\Parameter(name: 'quantity',in: 'query',description: 'Quantité de produit à retirer',schema: new OA\Schema(type: 'integer'))]
    public function removeFromPanier(Request $request, PanierRepository $panierRepo, ProduitRepository $produitRepo, EntityManagerInterface $entityManager, SerializerInterface $serializer): JsonResponse
    {
        //Les données ne doivent pas avoir status 0
        $produit = $produitRepo->find($request->get("idProduit"));
        $panier = $panierRepo->getUserActivePanier($request->get("idUser"));
        $quantity = $request->get("quantity");

        $lignesPanierList = $panier->getLignesPanier();
        $lp = null;
        $index = null;
        foreach ($lignesPanierList as $key => $value) {
            if($value->getProduit() == $produit){
                $lp = $value;
                $index = $key;
            }
        }
        //Si le produit n'est pas trouvé dans le panier
        if ($lp == null) {
            return new JsonResponse(array(), Response::HTTP_NOT_FOUND, [], false);
        }
        else{
            //Si la quantité a supprimer est supérieure a la quantité dans la panier
            if (($lp->getQuantity() - $quantity) < 1) {
                $lignesPanierList->removeElement($lp);
                $entityManager->persist($panier);
                $entityManager->flush();
                $context = SerializationContext::create()->setGroups(['getLignePanier']);
                $lignePanierJson = $serializer->serialize($lp, 'json', $context);
                return new JsonResponse($lignePanierJson, Response::HTTP_NO_CONTENT, [], true);
            }else {
                //Enlève la quantité du panier
                $lp->setQuantity($lp->getQuantity() - $quantity);
                $entityManager->persist($panier);
                $entityManager->flush();
                $context = SerializationContext::create()->setGroups(['getLignePanier']);
                $lignePanierJson = $serializer->serialize($lp, 'json', $context);
                return new JsonResponse($lignePanierJson, Response::HTTP_OK, [], true);
            }
        }
    }

    /**
     * Passe le panier actif d'un user en panier commandé.
     *
     * @param User $user
     * @return JsonResponse
     */
    #[OA\Parameter(name: 'idUser',in: 'query',description: 'id de l\'user à qui valider le panier',schema: new OA\Schema(type: 'string'))]
    #[Route('/panier/valider', name: 'validerPanier',methods: ['POST'])]
    public function validerPanier(Request $request, PanierRepository $panierRepo, UserRepository $userRepo, EntityManagerInterface $entityManager, SerializerInterface $serializer): JsonResponse
    {
        $requestContent = $request->toArray();
        $panierValidated = $panierRepo->getUserActivePanier($requestContent["idUser"]);

        //L'utilisateur est assigné un nouveau panier actif
        $panierValidated->setIsComplete(true);
        $user = $userRepo->find($requestContent["idUser"]);
        $panier = new Panier();
        $user->addPanier($panier);

        $entityManager->persist($user);
        $entityManager->persist($panierValidated);
        $entityManager->flush();
        
        $context = SerializationContext::create()->setGroups(['getPanier']);
        $lignePanierJson = $serializer->serialize($panierValidated, 'json', $context);
        return new JsonResponse($lignePanierJson, Response::HTTP_CREATED, [], true);
    }
}
