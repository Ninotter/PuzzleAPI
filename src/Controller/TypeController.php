<?php

namespace App\Controller;

use App\Repository\TypeRepository;
use App\Repository\ProduitRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class TypeController extends AbstractController
{
    /**
     * WIP: Route qui renvoie tout les types
     *
     * @param SerializerInterface $serializer
     * @param ProduitRepository $product
     * @return JsonResponse
     */
    #[Route('/type', name: 'type.getAll', methods: ['GET'])]
    public function getAllProduit(SerializerInterface $serializer, TypeRepository $product): JsonResponse
    {
        $type = $product->findAll();
        $typeJson = $serializer->serialize($type, 'json', ['groups' => ['getTypes']]);
        return new JsonResponse($typeJson, Response::HTTP_OK, [], false);
    }
}
