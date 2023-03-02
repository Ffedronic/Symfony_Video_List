<?php

namespace App\Controller;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use App\Repository\VideoRepository;
use App\Utils\AbstractClasses\CategoryTreeAbstract;
use App\Utils\CategoryTreeFrontPage;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
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

    #[Route('/video-list/category/{categoryname},{categoryid}/{page}', name: 'video_list', defaults: ["page" => 1])]
    public function videolist(CategoryTreeFrontPage $categories, $categoryid, VideoRepository $videoRepository, $page): Response
    {
        $categories->getCategoryListAndParent($categoryid);
        
        $ids = $categories->getChildIds($categoryid);
        array_push($ids, $categoryid);

        $videos = $videoRepository->findByChildIds($ids, $page);

       

        return $this->render('front/video_list.html.twig', [
            'subcategories' => $categories,
            'videos' => $videos
        ]);
    }

    #[Route('/video-details', name: 'video_details')]
    public function videodetails(): Response
    {
        return $this->render('front/video_details.html.twig');
    }

    #[Route('/search-results', methods: ["POST"], name: 'search_results')]
    public function searchresults(): Response
    {
        return $this->render('front/search_results.html.twig');
    }

    #[Route('/pricing', name: 'pricing')]
    public function pricing(): Response
    {
        return $this->render('front/pricing.html.twig');
    }

    #[Route('/register', name: 'register')]
    public function register(): Response
    {
        return $this->render('front/register.html.twig');
    }

    #[Route('/login', name: 'login')]
    public function login(): Response
    {
        return $this->render('front/login.html.twig');
    }

    #[Route('/payment', name: 'payment')]
    public function payment(): Response
    {
        return $this->render('front/payment.html.twig');
    }

    public function mainCategories(CategoryRepository $categoryRepository): Response
    {
        $categories = $categoryRepository->findBy(["parent" => null], ["name" => "ASC"]);
        return $this->render('front/main_categories.html.twig', ["categories" => $categories]);
    }
}
