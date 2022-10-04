<?php

namespace App\Controller;

use JsonSerializable;
use App\Entity\Produit;
use App\Repository\ProduitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Doctrine\Migrations\Configuration\Migration\JsonFile;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;


class ProduitController extends AbstractController
{

    // #[Route('/produit', name: 'app_produit')]
    // public function index(): JsonResponse
    // {
    //     return $this->json([
    //         'message' => 'Welcome to your new controller!',
    //         'path' => 'src/Controller/ProduitController.php',
    //     ]);
    // }

    // /**
    //  * Route qui renvoit le produit avec l'id passé en paramètre
    //  * 
    //  * @return JsonResponse
    //  */
    // #[Route('/produit/get/{id}', name: 'produit.get', methods: ['GET'])]
    // public function getProduitById(int $id, ProduitRepository $product, SerializerInterface $serializer): JsonResponse
    // {
    //     $produit = $product->find($id);
    //     $produitJson = $serializer->serialize($produit, 'json', ['Type' => ['id', 'nom']]);
    //     return $produit ?
    //     new JsonResponse($produitJson, Response::HTTP_OK, [], false) : 
    //     new JsonResponse(null, Response::HTTP_NOT_FOUND, [], false);
    // }

    /**
     * Route qui renvoit le produit avec l'id passé en paramètre
     * 
     * @return JsonResponse
     */
    #[Route('/produit/get/{idProduit}', name: 'produit.get', methods: ['GET'])]
    #[ParamConverter("produit", options : ["id" => "idProduit"])]
    public function getProduitById(Produit $produit, SerializerInterface $serializer): JsonResponse
    {
        $produitJson = $serializer->serialize($produit, 'json', ['groups' => ['getAllProduit']]);
        return new JsonResponse($produitJson, Response::HTTP_OK, [], true);
    }

    /**
     * Route qui renvoie touts les produits
     *
     * @param SerializerInterface $serializer
     * @param ProduitRepository $product
     * @return JsonResponse
     */
    #[Route('/produit', name: 'produit.getAll', methods: ['GET'])]
    public function getAllProduit(SerializerInterface $serializer, ProduitRepository $product): JsonResponse
    {
        $produit = $product->findAll();
        $produitJson = $serializer->serialize($produit, 'json', ['groups' => ['getProduit']]);
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
     * Met le statut d'un produit à 0
     *
     * @param SerializerInterface $serializer
     * @param ProduitRepository $product
     * @return JsonResponse
     */
    #[Route('/produit/{idProduit}', name: 'produit.turnoff', methods: ['DELETE'])]
    #[ParamConverter("produit", options : ["id" => "idProduit"])]
    public function deleteProduit(Produit $produit, EntityManagerInterface $entityManager): JsonResponse
    {
        if ($produit->isStatus() == false || $produit == null) {
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }else{
            $produit->setStatus(false);
            $entityManager->flush();
            return new JsonResponse(null, Response::HTTP_NO_CONTENT);
        }
    }
}
