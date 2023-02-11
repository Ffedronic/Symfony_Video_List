<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends AbstractController
{
    #[Route('/admin', name: 'admin_main_page')]
    public function index(): Response
    {
        return $this->render('admin/my_profile.html.twig');
    }

    #[Route('admin/categories', name: 'categories')]
    public function categories(): Response
    {
        return $this->render('admin/categories.html.twig');
    }

    #[Route('admin/videos', name: 'videos')]
    public function videos(): Response
    {
        return $this->render('admin/videos.html.twig');
    }

    #[Route('admin/upload_videos', name: 'upload_video')]
    public function upload_videos(): Response
    {
        return $this->render('admin/upload_video.html.twig');
    }

    #[Route('admin/users', name: 'users')]
    public function users(): Response
    {
        return $this->render('admin/users.html.twig');
    }

    #[Route('admin/edit_category', name: 'edit_category')]
    public function edit_category(): Response
    {
        return $this->render('admin/edit_category.html.twig');
    }
}
