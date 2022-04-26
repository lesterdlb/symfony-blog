<?php

declare(strict_types=1);

namespace App\BlogApp\Infrastructure\Controller;

use App\BlogApp\Application\Config\PostStatus;
use App\BlogApp\Application\UseCases\Post\FindPostsByValue;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api')]
class PostApiController extends AbstractController
{
    private FindPostsByValue $findPostsByValue;

    public function __construct(FindPostsByValue $findPostsByValue)
    {
        $this->findPostsByValue = $findPostsByValue;
    }

    #[Route('/posts', name: 'app_post_api')]
    public function index(Request $request): Response
    {
        $page     = $request->query->getInt('page', 1);
        $pageSize = $request->query->getInt('pageSize', 10);

        $posts = $this->findPostsByValue->execute(
            PostStatus::Draft->value,
            'status',
            $pageSize,
            $page
        );

        $data = [];
        foreach ($posts as $post) {
            $data[] = [
                'id'      => $post->getId(),
                'title'   => $post->getTitle(),
                'date'    => $post->getDate(),
                'content' => $post->getContent(),
                'status'  => $post->getStatus()
            ];
        }

        return $this->json($data);
    }
}
