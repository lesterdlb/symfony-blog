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
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[IsGranted('ROLE_EDITOR')]
class DashboardController extends AbstractController
{
    private PostRepository $postRepository;

    public function __construct(PostRepository $postRepository)
    {
        $this->postRepository = $postRepository;
    }

    #[Route('/dashboard', name: 'app_dashboard', methods: ['GET'])]
    public function index(Request $request): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $page = $request->query->get('page', 1);

        if ($this->isGranted(Roles::Moderator->value)) {
            $posts = $this->postRepository->findByValue(null, null, 10, $page);
        } else {
            $posts = $this->postRepository->findByValue(
                $user->getId(),
                'user',
                10,
                $page
            );
        }

        return $this->render('dashboard/index.html.twig', [
            'posts' => $posts
        ]);
    }

    #[Route('/dashboard/new-post', name: 'dashboard_post_new', methods: ['GET', 'POST'])]
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

            return $this->redirectToRoute('app_dashboard', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('dashboard/new.html.twig', [
            'post' => $post,
            'form' => $form,
        ]);
    }

    #[Route('/dashboard/{id}', name: 'dashboard_post_show', methods: ['GET', 'POST'])]
    public function show(Post $post, Request $request): Response
    {
        $form = $this->createFormBuilder()
                     ->add('newStatus', ChoiceType::class, [
                         'choices' => [
                             PostStatus::Draft->value     => PostStatus::Draft->value,
                             PostStatus::Published->value => PostStatus::Published->value,
                             PostStatus::Rejected->value  => PostStatus::Rejected->value,
                         ],
                         'label'   => 'Current Status:',
                         'data'    => $post->getStatus()
                     ])
                     ->add('send', SubmitType::class, ['label' => 'Change Status'])
                     ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $post->setStatus($form->get('newStatus')->getData());
            $this->postRepository->add($post);
        }

        return $this->render('dashboard/show.html.twig', [
            'post' => $post,
            'form' => $form->createView()
        ]);
    }

    #[Route('/dashboard/{id}/edit', name: 'dashboard_post_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Post $post): Response
    {
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->postRepository->add($post);

            return $this->redirectToRoute('app_dashboard', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('dashboard/edit.html.twig', [
            'post' => $post,
            'form' => $form,
        ]);
    }

    #[Route('/dashboard/{id}', name: 'dashboard_post_delete', methods: ['POST'])]
    public function delete(Request $request, Post $post): Response
    {
        if ($this->isCsrfTokenValid('delete' . $post->getId(), $request->request->get('_token'))) {
            $this->postRepository->remove($post);
        }

        return $this->redirectToRoute('app_dashboard', [], Response::HTTP_SEE_OTHER);
    }
}
