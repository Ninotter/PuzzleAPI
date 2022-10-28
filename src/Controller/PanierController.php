<?php

namespace App\Controller;

use App\Entity\User;
use JsonSerializable;
use App\Entity\Produit;
use App\Repository\ProduitRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PanierController extends AbstractController
{
    /**
     * Undocumented function
     *
     * @param User $user
     * @param Produit $produit
     * @return JsonResponse
     */
    #[Route('/panier/ajouter', name: 'ajouterPanier',methods: ['POST'])]
    public function addToPanier(Request $request, UserRepository $userRepo, ProduitRepository $produitRepo, EntityManagerInterface $entityManager): JsonResponse
    {
        $requestContent = $request->toArray();
        $user = $userRepo->find($requestContent["idUser"]);
        $produit = $produitRepo->find($requestContent["idProduit"]);

        $user->addIdProduit($produit);
        $entityManager->persist($user);
        $entityManager->flush();
        return new JsonResponse($user, Response::HTTP_CREATED, [], true);
    }
}
