<?php

namespace App\Controller;

use App\Config\PostStatus;
use App\Repository\PostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api')]
class PostApiController extends AbstractController
{
    private PostRepository $postRepository;

    public function __construct(PostRepository $postRepository)
    {
        $this->postRepository = $postRepository;
    }

    #[Route('/posts', name: 'app_post_api')]
    public function index(Request $request): Response
    {
        $page = $request->query->get('page', 1);
        $pageSize = $request->query->get('pageSize', 10);

        $posts = $this->postRepository->findByValue(
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
