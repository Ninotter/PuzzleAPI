<?php

namespace App\Controller;

use JsonSerializable;
use App\Entity\Produit;
use Doctrine\ORM\EntityManager;
use App\Repository\TypeRepository;
use App\Repository\ProduitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Doctrine\Migrations\Configuration\Migration\JsonFile;
use JMS\Serializer\Serializer;
use JMS\Serializer\SerializerInterface;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerContext;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Serializer\Serializer as SerializerSerializer;
use Symfony\Component\Serializer\SerializerInterface as SerializerSerializerInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

class ProduitController extends AbstractController
{
    /**
     * Route qui renvoie le produit avec l'id passé en paramètre
     * 
     * @param Produit $produit
     * @param SerializerInterface $serializer
     * @return JsonResponse
     */
    #[Route('/produit/{idProduit}', name: 'produit.get', methods: ['GET'])]
    #[ParamConverter("produit", options : ["id" => "idProduit"])]
    public function getProduitById(Produit $produit, SerializerInterface $serializer): JsonResponse
    {
        $context = SerializationContext::create()->setGroups(['getProduit']);
        $produitJson = $serializer->serialize($produit, 'json', $context);
        return new JsonResponse($produitJson, Response::HTTP_OK, [], true);
    }

    /**
     * Renvoie tous les produits
     * 
     * @param SerializerInterface $serializer
     * @param ProduitRepository $product
     * @return JsonResponse
     */
    #[Route('/produit', name: 'produit.getAll', methods: ['GET'])]
    public function getAllProduit(SerializerInterface $serializer, ProduitRepository $product, TagAwareCacheInterface $cache): JsonResponse
    {
        $produit = $product->findAll();
        $produitJson = $cache->get("getAllProduits", function (ItemInterface $item) use ($serializer, $product){
            $item->tag("produitCache");
            $cours = $product->findAll();
            $context = SerializationContext::create()->setGroups(['getAllProduit']);
            return $serializer->serialize($cours, 'json', $context);
        });
        return new JsonResponse($produitJson, Response::HTTP_OK, [], true);
    }

    // /**
    //  * Supprime un produit
    //  *
    //  * @param SerializerInterface $serializer
    //  * @param ProduitRepository $product
    //  * @return JsonResponse
    //  */
    // #[Route('/produit/delete/{idProduit}', name: 'produit.delete', methods: ['DELETE'])]
    // #[ParamConverter("produit", options : ["id" => "idProduit"])]
    // public function deleteProduit(Produit $produit, EntityManager $entityManager): JsonResponse
    // {
    //     $entityManager->remove($produit);
    //     $entityManager->flush();
    //     return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    // }

    /**
     * Rend un Produit Inactif
     *
     * @param Produit $produit
     * @param EntityManagerInterface $entityManager
     * @return JsonResponse
     */
    #[Route('/produit/{idProduit}', name: 'produit.turnOff', methods: ['DELETE'])]
    #[ParamConverter("produit", options : ["id" => "idProduit"])]
    public function deleteProduit(Produit $produit, EntityManagerInterface $entityManager, TagAwareCacheInterface $cache): JsonResponse
    {
        $cache->invalidateTags(["produitCache"]);
        if ($produit->isStatus() == false || $produit == null) {
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }else{
            $produit->setStatus(false);
            $entityManager->flush();
            return new JsonResponse(null, Response::HTTP_NO_CONTENT);
        }
    }

    /**
     * Retourne les produits supprimés
     *
     * @param SerializerInterface $serializer
     * @param ProduitRepository $product
     * @return JsonResponse
     */
    #[Route('/deleted/produit', name: 'produit.turnedOff', methods: ['GET'])]
    public function getDeletedProduits(SerializerInterface $serializer, ProduitRepository $product): JsonResponse
    {
        $deletedProduits = $product->getDeletedProduits();
        $context = SerializationContext::create()->setGroups(['getProduit']);
        $deletedProduitsJson = $serializer->serialize($deletedProduits, 'json', $context);
        return new JsonResponse($deletedProduitsJson, Response::HTTP_OK, [], true);
    }


    /**
     * Créé un produit
     *
     * @param TypeRepository $typeRepository
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @param SerializerInterface $serializer
     * @param UrlGeneratorInterface $urlGenerator
     * @param ValidatorInterface $validator
     * @return JsonResponse
     */
    #[Route('/produit', name: 'produit.create', methods: ['POST'])]
    #[IsGranted("ADMIN", message:"no access")]
    public function createProduit(TypeRepository $typeRepository, Request $request, EntityManagerInterface $entityManager, SerializerInterface $serializer, UrlGeneratorInterface $urlGenerator, ValidatorInterface $validator): JsonResponse
    {
        $produit = $serializer->deserialize(
            $request->getContent(),
            Produit::class,
            'json'
        );
        $produit->setStatus(true);

        $content = $request->toArray();
        $type = $typeRepository->find($content["idType"] ?? -1);
        $produit->setType($type);
        $errors = $validator->validate($produit);
        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }
        $entityManager->persist($produit);
        $entityManager->flush();

        $location = $urlGenerator->generate('produit.get', ['idProduit' => $produit->getId(), UrlGeneratorInterface::ABSOLUTE_URL ]);
        $context = SerializationContext::create()->setGroups(['getAllProduit']);
        $jsonProduit = $serializer->serialize($produit, 'json', $context);
        return new JsonResponse($jsonProduit, Response::HTTP_CREATED, ["Location" => $location], true);
    }
    //TESTS RAW BODY POSTMAN
    // {
    //     "nom": "testchangement",
    //     "prix" : 6,
    //     "niveauDifficulte" : 2,
    //     "nbPiece":  3,
    //     "tempsCompletion": 90,
    //     "idType": 38
    // }

    /** Fonction qui update les données d'un produit 
     * @param TypeRepository $typeRepository
     * @param Produit $produit
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @param SerializerInterface $serializer
     * @param UrlGeneratorInterface $urlGenerator
     * @return JsonResponse
     */
    #[Route('/produit/{idProduit}', name: 'produit.update', methods: ['PUT'])]
    #[ParamConverter("produit", options : ["id" => "idProduit"])]
    public function updateProduit(TypeRepository $typeRepository, Produit $produit, Request $request, EntityManagerInterface $entityManager, SerializerInterface $serializer, UrlGeneratorInterface $urlGenerator): JsonResponse
    {
        $updateProduit = $serializer->deserialize(
            $request->getContent(),
            Produit::class,
            'json'
        );
        $content = $request->toArray();
        $produit->setNom($updateProduit->getNom() ?? $produit->getNom());
        $produit->setPrix($updateProduit->getPrix() ?? $produit->getPrix());
        $produit->setNiveauDifficulte($updateProduit->getNiveauDifficulte() ?? $produit->getNiveauDifficulte());
        $produit->setNbPiece($updateProduit->getNbPiece() ?? $produit->getNbPiece());
        $produit->setTempsCompletion($updateProduit->getTempsCompletion() ?? $produit->getTempsCompletion());
        $produit->setDateCreation($updateProduit->getDateCreation() ?? $produit->getDateCreation());
        $produit->setPaysOrigine($updateProduit->getPaysOrigine() ?? $produit->getPaysOrigine());
        // a refaire si besoin plus tard, quand produit aura des sous-objets
        // if(array_key_exists('idPanier', $content) && $content["idPanier"]){
        //     $panierRepository->find($content["idPanier"]);
        // }

        $updateProduit->setStatus(true);

        $content =$request->toArray();
        $type = $typeRepository->find($content["idType"] ?? -1);
        $produit->setType($type);
        $entityManager->persist($produit);
        $entityManager->flush();

        $location = $urlGenerator->generate('produit.get', ['idProduit' => $updateProduit->getId(), UrlGeneratorInterface::ABSOLUTE_URL ]);
        $context = SerializationContext::create()->setGroups(['getProduit']);

        $jsonProduit = $serializer->serialize($produit, 'json', $context);
        return new JsonResponse($jsonProduit, Response::HTTP_CREATED, ["Location" => $location], true);
    }
}
