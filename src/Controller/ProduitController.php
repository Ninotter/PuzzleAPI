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
use OpenApi\Attributes as OA;

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
     * Renvoie tous les produits avec un filtre
     */
    #[OA\Parameter(name: 'nom',in: 'query',description: 'Nom à filtrer',schema: new OA\Schema(type: 'string'))]
    #[OA\Parameter(name: 'prix',in: 'query',description: 'Trie les prix par ordre croissant/décroissant(ASC/DESC)',schema: new OA\Schema(type: 'string', enum:['ASC', 'DESC']))]
    #[OA\Parameter(name: 'niveau_difficulte',in: 'query',description: 'Trie le niveau de difficulté par ordre croissant/décroissant(ASC/DESC)',schema: new OA\Schema(type: 'string',enum:['ASC', 'DESC']))]
    #[OA\Parameter(name: 'nb_piece',in: 'query',description: 'Trie le nombre de pièce par ordre croissant/décroissant(ASC/DESC)',schema: new OA\Schema(type: 'string',enum:['ASC', 'DESC']))]
    #[OA\Parameter(name: 'temps_completion',in: 'query',description: 'Trie les temps de complétion par ordre croissant/décroissant(ASC/DESC)',schema: new OA\Schema(type: 'string',enum:['ASC', 'DESC']))]
    #[Route('/produit', name: 'produit.getAll', methods: ['GET'])]
    public function getAllProduit(SerializerInterface $serializer, ProduitRepository $produitRepository, TagAwareCacheInterface $cache,Request $request): JsonResponse
    {
        $nom = $request->query->get("nom") ? $request->query->get("nom") : "";
        $prix = $request->query->get("prix") ? $request->query->get("prix") : ""; 
        $niveauDifficulte = $request->query->get("niveau_difficulte") ? $request->query->get("niveau_difficulte") : "";
        $nbPiece = $request->query->get("nb_piece") ? $request->query->get("nb_piece") : "";
        $tempsCompletion = $request->query->get("temps_completion") ? $request->query->get("temps_completion") : "";
        $produit = $produitRepository->getAllProduitsFiltre($nom,$prix,$niveauDifficulte,$nbPiece,$tempsCompletion);
        $context = SerializationContext::create()->setGroups(['getAllProduit']);
        $produitJson =  $serializer->serialize($produit, 'json', $context);

        // $produitJson = $cache->get("getAllProduits", function (ItemInterface $item) use ($serializer, $produitRepository){
        //     echo 'mise en cache';
        //     $item->tag("produitCache");
        //     $produit = $produitRepository->findAll();
        //     $context = SerializationContext::create()->setGroups(['getAllProduit']);
        //     return $serializer->serialize($produit, 'json', $context);
        // });
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
     * Rend un produit inactif
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

    /** Route qui update les données d'un produit 
     * NE FONCTIONNE PAS DEPUIS NELMIO. UNIQUEMENT TESTABLE AVEC POSTMAN
     * 
     * @param TypeRepository $typeRepository
     * @param Produit $produit
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @param SerializerInterface $serializer
     * @param UrlGeneratorInterface $urlGenerator
     * @return JsonResponse
     */
    #[OA\Parameter(name: 'nom',in: 'query',description: 'nom à changer',schema: new OA\Schema(type: 'string', minimum: 3))]
    #[OA\Parameter(name: 'prix',in: 'query',description: 'prix à changer',schema: new OA\Schema(type: 'float'))]
    #[OA\Parameter(name: 'niveauDifficulte',in: 'query',description: 'nom à changer',schema: new OA\Schema(type: 'integer', minimum:1, maximum : 5))]
    #[OA\Parameter(name: 'nbPiece',in: 'query',description: 'nombre de pièces à changer',schema: new OA\Schema(type: 'integer', minimum: 1, maximum: 60))]
    #[OA\Parameter(name: 'tempsCompletion',in: 'query',description: 'temps de completion à changer',schema: new OA\Schema(type: 'integer', minimum:1))]
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
