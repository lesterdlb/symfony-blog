<?php

namespace App\BlogApp\Infrastructure\Controller;

use App\BlogApp\Application\Post\GetPosts;
use App\BlogApp\Domain\PostRepositoryInterface;
use App\Config\PostStatus;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PostController extends AbstractController
{
    private PostRepositoryInterface $postRepository;
    private GetPosts $getPosts;

    public function __construct(PostRepositoryInterface $postRepository, GetPosts $getPosts)
    {
        $this->postRepository = $postRepository;
        $this->getPosts       = $getPosts;
    }

    #[Route('/{_locale}/posts/', name: 'app_posts', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $page = $request->query->get('page', 1);

        $posts = $this->getPosts->execute(
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
