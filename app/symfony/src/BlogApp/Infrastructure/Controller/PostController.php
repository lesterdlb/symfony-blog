<?php

declare(strict_types=1);

namespace App\BlogApp\Infrastructure\Controller;

use App\BlogApp\Application\Config\PostStatus;
use App\BlogApp\Application\UseCases\Post\FindPostsByValue;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PostController extends AbstractController
{
    private FindPostsByValue $findPostsByValue;

    public function __construct(FindPostsByValue $findPostsByValue)
    {
        $this->findPostsByValue = $findPostsByValue;
    }

    #[Route('/{_locale}/posts/', name: 'app_posts', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $page = $request->query->get('page', 1);

        $posts = $this->findPostsByValue->execute(
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
