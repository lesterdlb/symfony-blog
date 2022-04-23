<?php

declare(strict_types=1);

namespace App\BlogApp\Infrastructure\Controller;

use App\BlogApp\Application\Config\PostStatus;
use App\BlogApp\Application\Config\Roles;
use App\BlogApp\Application\UseCases\Post\CreateUpdatePost;
use App\BlogApp\Application\UseCases\Post\FindOnePostById;
use App\BlogApp\Application\UseCases\Post\FindPostsByValue;
use App\BlogApp\Application\UseCases\Post\RemovePost;
use App\BlogApp\Application\Form\PostFormType;

use App\BlogApp\Domain\Entity\Post;
use App\BlogApp\Domain\Entity\User;
use App\BlogApp\Domain\Event\PostReviewed;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Routing\Annotation\Route;

#[IsGranted('ROLE_EDITOR')]
class DashboardController extends AbstractController
{
    private FindPostsByValue $findPostsByValue;
    private CreateUpdatePost $createUpdatePost;
    private FindOnePostById $findOnePostById;
    private RemovePost $removePost;
    private EventDispatcherInterface $dispatcher;

    public function __construct(
        FindPostsByValue $findPostsByValue,
        CreateUpdatePost $createUpdatePost,
        FindOnePostById $findOnePostById,
        RemovePost $removePost,
        EventDispatcherInterface $dispatcher,
    ) {
        $this->dispatcher = $dispatcher;
        $this->findPostsByValue = $findPostsByValue;
        $this->createUpdatePost = $createUpdatePost;
        $this->findOnePostById = $findOnePostById;
        $this->removePost = $removePost;
    }

    #[Route('/{_locale}/dashboard/', name: 'app_dashboard', methods: ['GET'])]
    public function index(Request $request): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $page = $request->query->get('page', 1);

        if ($this->isGranted(Roles::Moderator->value)) {
            $posts = $this->findPostsByValue->execute(null, null, 10, $page);
        } else {
            $posts = $this->findPostsByValue->execute(
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

    #[Route('/{_locale}/dashboard/new-post', name: 'dashboard_post_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $post = new Post();
        $form = $this->createForm(PostFormType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var User $user */
            $user = $this->getUser();

            $post->setDate(new \DateTime());
            $post->setStatus(
                $this->isGranted(Roles::Moderator->value) ?
                    PostStatus::Published->value : PostStatus::Draft->value
            );
            $post->setUser($user);

            $this->createUpdatePost->execute($post, $user->getId());

            return $this->redirectToRoute('app_dashboard', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('dashboard/new.html.twig', [
            'post' => $post,
            'form' => $form,
        ]);
    }

    #[Route('/{_locale}/dashboard/{id}', name: 'dashboard_post_show', methods: ['GET', 'POST'])]
    public function show(int $id, Request $request): Response
    {
        $post = $this->findOnePostById->execute($id);
        $this->denyAccessUnlessGranted('view', $post);

        $form = $this->createPostStatusForm($post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var User $user */
            $user = $this->getUser();

            $buttonName = $form->getClickedButton()->getName();
            if ($buttonName === PostStatus::Published->name) {
                $newStatus = PostStatus::Published->value;
            } else {
                $newStatus = PostStatus::Rejected->value;
            }
            $post->setStatus($newStatus);

            $this->createUpdatePost->execute($post, $user->getId());

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
            /** @var User $user */
            $user = $this->getUser();

            $this->createUpdatePost->execute($post, $user->getId());

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
            /** @var User $user */
            $user = $this->getUser();

            $this->removePost->execute($post, $user->getId());
        }

        return $this->redirectToRoute('app_dashboard', [], Response::HTTP_SEE_OTHER);
    }

    private function createPostStatusForm(Post $post): FormInterface
    {
        $form = $this->createFormBuilder()
                     ->add(
                         PostStatus::Published->name, SubmitType::class, [
                             'label' => PostStatus::Published->value,
                             'attr'  => ['class' => 'btn btn-success']
                         ]
                     )
                     ->add(
                         PostStatus::Rejected->name, SubmitType::class, [
                             'label' => PostStatus::Rejected->value,
                             'attr'  => ['class' => 'btn btn-danger']
                         ]
                     );

        if ($post->getStatus() !== PostStatus::Draft->value) {
            $form->remove(PostStatus::from($post->getStatus())->name);
        }

        return $form->getForm();
    }
}
