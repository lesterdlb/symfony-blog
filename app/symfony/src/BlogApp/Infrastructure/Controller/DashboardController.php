<?php

declare(strict_types=1);

namespace App\BlogApp\Infrastructure\Controller;

use App\BlogApp\Application\Config\PostStatus;
use App\BlogApp\Application\Config\Roles;
use App\BlogApp\Application\Form\StatusFormType;
use App\BlogApp\Application\UseCases\Post\CreatePost;
use App\BlogApp\Application\UseCases\Post\UpdatePost;
use App\BlogApp\Application\UseCases\Post\FindOnePostById;
use App\BlogApp\Application\UseCases\Post\FindPostsByValue;
use App\BlogApp\Application\UseCases\Post\RemovePost;
use App\BlogApp\Application\Form\PostFormType;

use App\BlogApp\Domain\Event\PostReviewed;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Routing\Annotation\Route;

#[IsGranted('ROLE_EDITOR')]
class DashboardController extends AbstractController
{
    private FindPostsByValue $findPostsByValue;
    private CreatePost $createPost;
    private UpdatePost $updatePost;
    private FindOnePostById $findOnePostById;
    private RemovePost $removePost;
    private EventDispatcherInterface $dispatcher;

    public function __construct(
        FindPostsByValue $findPostsByValue,
        CreatePost $createPost,
        UpdatePost $updatePost,
        FindOnePostById $findOnePostById,
        RemovePost $removePost,
        EventDispatcherInterface $dispatcher,
    ) {
        $this->dispatcher       = $dispatcher;
        $this->createPost       = $createPost;
        $this->findPostsByValue = $findPostsByValue;
        $this->updatePost       = $updatePost;
        $this->findOnePostById  = $findOnePostById;
        $this->removePost       = $removePost;
    }

    #[Route('/{_locale}/dashboard/', name: 'app_dashboard', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $page = $request->query->getInt('page', 1);

        if ($this->isGranted(Roles::Moderator->value)) {
            $posts = $this->findPostsByValue->execute(null, null, 10, $page);
        } else {
            $posts = $this->findPostsByValue->execute(
                $this->getUser()->getId(),
                'user',
                10,
                $page
            );
        }

        return $this->render('dashboard/index.html.twig', [
            'posts' => $posts
        ]);
    }

    #[Route('/{_locale}/dashboard/new-post', name: 'dashboard_post_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $form = $this->createForm(PostFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $this->createPost->execute(
                $data->getTitle(),
                $data->getContent(),
                $this->isGranted(Roles::Moderator->value) ?
                    PostStatus::Published->value : PostStatus::Draft->value,
                $this->getUser()
            );

            return $this->redirectToRoute('app_dashboard', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('dashboard/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/{_locale}/dashboard/{id}', name: 'dashboard_post_show', methods: ['GET', 'POST'])]
    public function show(int $id, Request $request): Response
    {
        $post = $this->findOnePostById->execute($id);
        $this->denyAccessUnlessGranted('view', $post);

        $form = $this->createForm(StatusFormType::class, null, ['post' => $post]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->getUser();

            $buttonName = $form->getClickedButton()->getName();
            if ($buttonName === PostStatus::Published->name) {
                $newStatus = PostStatus::Published->value;
            } else {
                $newStatus = PostStatus::Rejected->value;
            }
            $post->setStatus($newStatus);

            $this->updatePost->execute($post, $user->getId());

            $this->dispatcher->dispatch(new PostReviewed($post, $user->getEmail()));

            return $this->redirect($request->getUri());
        }

        return $this->render('dashboard/show.html.twig', [
            'post' => $post,
            'form' => $form->createView()
        ]);
    }

    #[Route('/{_locale}/dashboard/edit/{id}', name: 'dashboard_post_edit', methods: ['GET', 'POST'])]
    public function edit(int $id, Request $request): Response
    {
        $post = $this->findOnePostById->execute($id);
        $this->denyAccessUnlessGranted('edit', $post);

        $form = $this->createForm(PostFormType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->getUser();

            $this->updatePost->execute($post, $user->getId());

            return $this->redirectToRoute('app_dashboard', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('dashboard/edit.html.twig', [
            'post' => $post,
            'form' => $form,
        ]);
    }

    #[Route('/{_locale}/dashboard/delete/{id}', name: 'dashboard_post_delete', methods: ['POST'])]
    public function delete(int $id, Request $request): Response
    {
        $post = $this->findOnePostById->execute($id);
        $this->denyAccessUnlessGranted('delete', $post);

        if ($this->isCsrfTokenValid('delete' . $post->getId(), $request->request->get('_token'))) {
            $user = $this->getUser();

            $this->removePost->execute($post, $user->getId());
        }

        return $this->redirectToRoute('app_dashboard', [], Response::HTTP_SEE_OTHER);
    }
}
