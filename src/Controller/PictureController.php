<?php

namespace App\Controller;

use App\Entity\Picture;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PictureController extends AbstractController
{
    // #[Route('/picture', name: 'app_picture')]
    // public function index(): JsonResponse
    // {
    //     return $this->json([
    //         'message' => 'Welcome to your new controller!',
    //         'path' => 'src/Controller/PictureController.php',
    //     ]);
    // }

    #[Route('/picture/', name: 'picture.create', methods:['POST'])]
    public function createPicture(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $picture = new Picture();
        $files = $request->files->get('file');
        $picture->setFile($files);
        $picture->setMimeType($files->getMimeType());
        $picture->setRealName($files->getClientOriginalName());
        $picture->setPublicPath('/assets/picture');
        $picture->setStatus(true);
        $picture->setUploadDate(new \DateTime());
        
        $entityManager->persist($picture);
        $entityManager->flush();
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/PictureController.php',
        ]);
    }
}
