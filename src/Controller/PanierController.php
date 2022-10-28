<?php

namespace App\Controller;

use App\Entity\User;
use JsonSerializable;
use App\Entity\Produit;
use App\Repository\ProduitRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
// use JMS\Serializer\Serializer;
// use JMS\Serializer\SerializerInterface;
// use JMS\Serializer\SerializerContext;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;

class PanierController extends AbstractController
{
    /**
     * Ajoute un produit défini dans le body à un user défini dans le body
     *
     * @param User $user
     * @param Produit $produit
     * @return JsonResponse
     */
    #[Route('/panier/ajouter', name: 'ajouterPanier',methods: ['POST'])]
    public function addToPanier(Request $request, UserRepository $userRepo, ProduitRepository $produitRepo, EntityManagerInterface $entityManager, SerializerInterface $serializer): JsonResponse
    {
        //Les données ne doivent pas avoir status 0
        $requestContent = $request->toArray();
        $user = $userRepo->find($requestContent["idUser"]);
        $produit = $produitRepo->find($requestContent["idProduit"]);

        $user->addIdProduit($produit);
        $entityManager->persist($user);
        $entityManager->flush();
        $userJson = $serializer->deserialize(
            $request->getContent(),
            Produit::class,
            'json'
        );
        return new JsonResponse($userJson, Response::HTTP_CREATED, [], true);
    }
}
