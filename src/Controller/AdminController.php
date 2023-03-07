<?php

namespace App\Controller;

use App\Entity\Category;
use App\Form\CategoryType;
use App\Repository\CategoryRepository;
use App\Utils\CategoryTreeAdminList;
use App\Utils\CategoryTreeAdminOptionList;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends AbstractController
{
    #[Route('/admin', name: 'admin_main_page')]
    public function index(): Response
    {
        return $this->render('admin/my_profile.html.twig');
    }

    #[Route('admin/su/categories', name: 'categories', methods: ["GET", "POST"])]
    public function categories(CategoryTreeAdminList $categories, Request $request, CategoryRepository $categoryRepository): Response
    {
        $categories->getCategoryList($categories->buildTree());

        $category = new Category();
        $form = $this->createForm(CategoryType::class, $category);

        if ($this->saveCategory($form, $request, $category, $categoryRepository)) {
           
            return $this->redirectToRoute('categories');

        }

        return $this->render('admin/categories.html.twig', ["categories" => $categories->categorylist, 'form' => $form->createView()]);
    }

    #[Route('admin/videos', name: 'videos')]
    public function videos(): Response
    {
        return $this->render('admin/videos.html.twig');
    }

    #[Route('admin/su/upload_videos', name: 'upload_video')]
    public function upload_videos(): Response
    {
        return $this->render('admin/upload_video.html.twig');
    }

    #[Route('admin/su/users', name: 'users')]
    public function users(): Response
    {
        return $this->render('admin/users.html.twig');
    }

    #[Route('admin/su/edit_category/{id}', name: 'edit_category', methods: ["GET", "POST"])]
    public function edit_category(Category $category, Request $request, CategoryRepository $categoryRepository): Response
    {
        $form = $this->createForm(CategoryType::class, $category);
        if ($this->saveCategory($form, $request, $category, $categoryRepository)) {
           
            return $this->redirectToRoute('categories');

        }
        dump($category);
        return $this->render('admin/edit_category.html.twig', ["category" => $category, 'form' => $form->createView()]);
    }

    #[Route('admin/su/delete_category/{id}', name: 'delete_category')]
    public function delete_category(Category $category, ManagerRegistry $doctrine)
    {
        $entityManager = $doctrine->getManager();
        $entityManager->remove($category);
        $entityManager->flush();


        return $this->redirectToRoute("categories");
    }

    public function getAllCategories(CategoryTreeAdminOptionList $categories, $editedCategory = null): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $categories->getCategoryList($categories->buildTree());
        return $this->render("admin/all_categories.html.twig", ["categories" => $categories->categorylist, "editedCategory" => $editedCategory]);
    }

    private function saveCategory($form, $request, $category, $categoryRepository){
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            //save category
            $category->setName($request->request->get('category')['name']);
            $parent = $categoryRepository->find($request->request->get('category')['parent']);
            $category->setParent($parent);
            $categoryRepository->save($category, true);
            return true;

        } else {
            return false;
        }
    }
}

