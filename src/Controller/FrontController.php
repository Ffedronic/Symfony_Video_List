<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FrontController extends AbstractController
{
    #[Route('/', name: 'main_page')]
    public function index(): Response
    {
        return $this->render('front/index.html.twig', [
            'controller_name' => 'FrontController',
        ]);
    }

    #[Route('/video-list', name: 'video_list')]
    public function videolist(): Response
    {
        return $this->render('front/video_list.html.twig');
    }

    #[Route('/video-details', name: 'video_details')]
    public function videodetails(): Response
    {
        return $this->render('front/video_details.html.twig');
    }
}
