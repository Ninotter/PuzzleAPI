<?php

namespace App\Controller;

use App\Entity\Picture;
use App\Repository\PictureRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Routing\Generator\UrlGenerator;

class PictureController extends AbstractController
{
    #[Route('/picture/', name: 'picture.create', methods:['POST'])]
    public function createPicture(Request $request, EntityManagerInterface $entityManager, UrlGeneratorInterface $urlGenerator, SerializerInterface $serializer): JsonResponse
    {
        $picture = new Picture();
        $files = $request->files->get('file');
        $picture->setFile($files);
        $picture->setMimeType($files->getMimeType());
        $picture->setRealName($files->getClientOriginalName());
        $picture->setPublicPath('/assets/pictures');
        $picture->setStatus(true);
        $picture->setUploadDate(new \DateTime());
        
        $entityManager->persist($picture);
        $entityManager->flush();

        $pictureJson = $serializer->serialize($picture, 'json', ['groups' => ['getPicture']]);
        $location = $urlGenerator->generate('picture.get', ['idPicture' => $picture->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
        return new JsonResponse($pictureJson, Response::HTTP_OK, ["Location" => $location], true);
    }

    /**
     * Route qui renvoie la picture avec l'id passé en paramètre
     * 
     * @param Picture $picture
     * @param SerializerInterface $serializer
     * @return JsonResponse
     */
    #[Route('/picture/{idPicture}', name: 'picture.get', methods: ['GET'])]
    #[ParamConverter("picture", options : ["id" => "idPicture"], class:'App\Entity\Picture')]
    public function getPicture(Picture $picture, SerializerInterface $serializer, Request $request): JsonResponse
    {
        $relativeLocation = $picture->getPublicPath() . '/' . $picture->getRealPath();
        $location = $request->getUriForPath('/');
        $location = $location . str_replace('/assets','assets', $relativeLocation);

        return new JsonResponse($serializer->serialize($picture,'json', ['groups' => 'getPicture']), Response::HTTP_OK, ['Location' => $location], true);

        // $pictureJson = $serializer->serialize($picture, 'json', ['groups' => ['getPicture']]);
        // return new JsonResponse($pictureJson, Response::HTTP_OK, [], true);
    }
}
