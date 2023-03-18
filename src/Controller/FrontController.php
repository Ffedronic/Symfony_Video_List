<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\User;
use App\Entity\Video;
use App\Form\RegisterType;
use App\Repository\CategoryRepository;
use App\Repository\UserRepository;
use App\Repository\VideoRepository;
use App\Utils\CategoryTreeFrontPage;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
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

    #[Route('/video-details/{video}', name: 'video_details')]
    public function videodetails($video, VideoRepository $videoRepository): Response
    {
        dump($videoRepository->findVideoDetails($video));
        return $this->render('front/video_details.html.twig', ['video' => $videoRepository->findVideoDetails($video)]);
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

    #[Route('/new-comment/{video}', name: 'new_comment')]
    public function newComment(Video $video, Request $request, ManagerRegistry $doctrine)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

        if (!empty(trim($request->get('comment')))) {
            $comment = new Comment();
            $comment->setContent($request->get('comment'));
            $comment->setUser($this->getUser());
            $comment->setVideo($video);

            $entitymanager = $doctrine->getManager();
            $entitymanager->persist($comment);
            $entitymanager->flush();
        }

        return $this->redirectToRoute('video_details', ['video' => $video->getId()]);
    }

    #[Route('/video-list/{video}/like', name: 'like_video', methods: ["POST"])]
    #[Route('/video-list/{video}/dislike', name: 'dislike_video', methods: ["POST"])]
    #[Route('/video-list/{video}/unlike', name: 'undo_like_video', methods: ["POST"])]
    #[Route('/video-list/{video}/undodislike', name: 'undo_dislike_video', methods: ["POST"])]
    public function toggleLikesAjax(Video $video, Request $request, EntityManagerInterface $entitymanager)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        switch ($request->get("_route")) {
            case 'like_video':
                $result = $this->likeVideo($video, $entitymanager);
                break;
            case 'dislike_video':
                $result = $this->dislikeVideo($video, $entitymanager);
                break;
            case 'undo_like_video':
                $result = $this->undoLikeVideo($video, $entitymanager);
                break;
            case 'undo_dislike_video':
                $result = $this->undoDislikeVideo($video, $entitymanager);
                break;
            default:
                # code...
                break;
        }

        return $this->json(['action' => $result, 'id' => $video->getId()]);
    }

    private function likeVideo($video, $entitymanager){
        $user = $entitymanager->getRepository(User::class)->find($this->getUser());
        $user -> addLikedVideo($video);
        
        $entitymanager->persist($user);
        $entitymanager->flush();
        
        return 'liked';
    }

    private function dislikeVideo($video, $entitymanager){
        $user = $entitymanager->getRepository(User::class)->find($this->getUser());
        $user -> addDislikedVideo($video);
        
        $entitymanager->persist($user);
        $entitymanager->flush();
        
        return 'disliked';
    }

    private function undoLikeVideo($video, $entitymanager){
        $user = $entitymanager->getRepository(User::class)->find($this->getUser());
        $user -> removeLikedVideo($video);
        
        $entitymanager->persist($user);
        $entitymanager->flush();

        return 'undo liked';
    }

    private function undoDislikeVideo($video, $entitymanager){
        $user = $entitymanager->getRepository(User::class)->find($this->getUser());
        $user -> removeDislikedVideo($video);
        
        $entitymanager->persist($user);
        $entitymanager->flush();

        return 'undo disliked';
    }

    public function mainCategories(CategoryRepository $categoryRepository): Response
    {
        $categories = $categoryRepository->findBy(["parent" => null], ["name" => "ASC"]);
        return $this->render('front/main_categories.html.twig', ["categories" => $categories]);
    }
}
