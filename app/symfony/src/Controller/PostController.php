<?php

namespace App\Controller;

use App\Config\PostStatus;
use App\Config\Roles;
use App\Entity\Post;
use App\Entity\User;
use App\Form\PostType;
use App\Repository\PostRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
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

    #[Route('/posts', name: 'app_posts', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $page = $request->query->get('page', 1);

        $posts = $this->postRepository->findByStatus(
            PostStatus::Published->value,
            10,
            $page
        );

        return $this->render('post/index.html.twig', [
            'posts' => $posts
        ]);
    }

    #[IsGranted('ROLE_EDITOR')]
    #[Route('/dashboard', name: 'app_dashboard', methods: ['GET'])]
    public function dashboard(Request $request): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $page = $request->query->get('page', 1);

        $posts = $this->postRepository->findByUserId(
            $user->getId(),
            10,
            1
        );

        return $this->render('post/dashboard.html.twig', [
            'posts' => $posts
        ]);
    }

    #[Route('/new', name: 'app_post_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $post = new Post();
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $post->setDate(new \DateTime());
            $post->setStatus(PostStatus::Draft->value);
            $post->setUser($this->getUser());
            $this->postRepository->add($post);

            return $this->redirectToRoute('app_post_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('post/new.html.twig', [
            'post' => $post,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_post_show', methods: ['GET'])]
    public function show(Post $post): Response
    {
        return $this->render('post/show.html.twig', [
            'post' => $post,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_post_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Post $post): Response
    {
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->postRepository->add($post);

            return $this->redirectToRoute('app_post_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('post/edit.html.twig', [
            'post' => $post,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_post_delete', methods: ['POST'])]
    public function delete(Request $request, Post $post): Response
    {
        if ($this->isCsrfTokenValid('delete' . $post->getId(), $request->request->get('_token'))) {
            $this->postRepository->remove($post);
        }

        return $this->redirectToRoute('app_post_index', [], Response::HTTP_SEE_OTHER);
    }
}
