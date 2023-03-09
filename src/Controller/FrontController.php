<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegisterType;
use App\Repository\CategoryRepository;
use App\Repository\UserRepository;
use App\Repository\VideoRepository;
use App\Utils\CategoryTreeFrontPage;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

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
    public function videolist(CategoryTreeFrontPage $categories, $categoryid, VideoRepository $videoRepository, $page, Request $request): Response
    {
        $categories->getCategoryListAndParent($categoryid);

        $ids = $categories->getChildIds($categoryid);
        array_push($ids, $categoryid);

        $videos = $videoRepository->findByChildIds($ids, $page, $request->get('sortby'));

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

    #[Route('/search-results/{page}', defaults: ['page' => 1], methods: ["GET"], name: 'search_results')]
    public function searchresults(VideoRepository $videoRepository, $page, Request $request): Response
    {
        $videos = null;
        $query = null;

        if ($query = $request->get('query')) {
            $videos = $videoRepository->findByTitle($query, $page, $request->get('sortby'));
            if (!$videos->getItems()) {
                $videos = null;
            }
        }

        return $this->render('front/search_results.html.twig', ['videos' => $videos, 'query' => $query]);
    }

    #[Route('/pricing', name: 'pricing')]
    public function pricing(): Response
    {
        return $this->render('front/pricing.html.twig');
    }

    #[Route('/register', name: 'register')]
    public function register(Request $request, UserPasswordHasherInterface $password_encoder, UserRepository $userRepository): Response
    {
        $user = new User;
        $form = $this->createForm(RegisterType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user->setName($request->request->get('register')['name']);
            $user->setLastName($request->request->get('register')['last_name']);
            $user->setEmail($request->request->get('register')['email']);
            $password = $password_encoder->hashPassword($user, $request->request->get('register')['password']['first']);
            $user->setPassword($password);
            $user->setRoles(['ROLE_USER']);

            $userRepository->save($user, true);

            $this->loginUserAutomatically($user, $password);

            return $this->redirectToRoute('admin_main_page');
        }
        return $this->render('front/register.html.twig', ["form" => $form->createView()]);
    }

    private function loginUserAutomatically($user, $password)
    {
        $token = new UsernamePasswordToken(
            $user,
            $password,
            'main', // security.yaml
            $user->getRoles()
        );
        $this->get('security.token_storage')->setToken($token);
        $this->get('session')->set('_security_main', serialize($token));
    }

    #[Route('/login', name: 'login')]
    public function login(AuthenticationUtils $helper): Response
    {
        return $this->render('front/login.html.twig', ["error" => $helper->getLastAuthenticationError()]);
    }

    #[Route('/logout', name: 'logout')]
    public function logout(): void
    {
        throw new \Exception("this should never be reached!");
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
