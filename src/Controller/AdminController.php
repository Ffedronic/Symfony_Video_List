<?php

namespace App\Controller;

use App\Entity\Category;
use App\Utils\CategoryTreeAdminList;
use App\Utils\CategoryTreeAdminOptionList;
use Doctrine\Persistence\ManagerRegistry;
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
    public function categories(CategoryTreeAdminList $categories): Response
    {
        $categories->getCategoryList($categories->buildTree());
        return $this->render('admin/categories.html.twig', ["categories" => $categories->categorylist]);
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

    #[Route('admin/delete_category/{id}', name: 'delete_category')]
    public function delete_category(Category $category, ManagerRegistry $doctrine)
    {
        $entityManager = $doctrine->getManager();
        $entityManager->remove($category);
        $entityManager->flush();


        return $this->redirectToRoute("categories");
    }

    public function getAllCategories(CategoryTreeAdminOptionList $categories): Response
    {
        $categories->getCategoryList($categories->buildTree());
        return $this->render("admin/all_categories.html.twig", ["categories" => $categories->categorylist]);
    }
}
