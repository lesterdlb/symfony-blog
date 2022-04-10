<?php

namespace App\Controller;

use App\Config\PostStatus;
use App\Repository\PostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PostController extends AbstractController
{
    private PostRepository $postRepository;

    public function __construct(PostRepository $postRepository)
    {
        $this->postRepository = $postRepository;
    }

    #[Route('/{_locale}/posts/', name: 'app_posts', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $page = $request->query->get('page', 1);

        $posts = $this->postRepository->findByValue(
            PostStatus::Published->value,
            'status',
            10,
            $page
        );

        return $this->render('post/index.html.twig', [
            'posts' => $posts
        ]);
    }
}
