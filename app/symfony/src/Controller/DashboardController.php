<?php

namespace App\Controller;

use App\Config\PostStatus;
use App\Config\Roles;
use App\Entity\Post;
use App\Entity\User;
use App\Form\PostType;
use App\Repository\PostRepository;
use App\Event\PostReviewed;
use Psr\Log\LoggerInterface;
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
    private PostRepository $postRepository;
    private EventDispatcherInterface $dispatcher;
    private LoggerInterface $logger;

    public function __construct(
        PostRepository $postRepository,
        EventDispatcherInterface $dispatcher,
        LoggerInterface $logger
    ) {
        $this->postRepository = $postRepository;
        $this->dispatcher     = $dispatcher;
        $this->logger         = $logger;
    }

    #[Route('/{_locale}/dashboard/', name: 'app_dashboard', methods: ['GET'])]
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

    #[Route('/{_locale}/dashboard/new-post', name: 'dashboard_post_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $post = new Post();
        $form = $this->createForm(PostType::class, $post);
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
            $this->postRepository->add($post);

            $this->logger->info(sprintf('The user %s created a new article.', $user->getUserIdentifier()));

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
        /** @var Post $post */
        $post = $this->postRepository->findOneById($id);
        $this->denyAccessUnlessGranted('view', $post);

        $form = $this->createPostStatusForm($post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $buttonName = $form->getClickedButton()->getName();
            if ($buttonName === PostStatus::Published->name) {
                $newStatus = PostStatus::Published->value;
            } else {
                $newStatus = PostStatus::Rejected->value;
            }

            $post->setStatus($newStatus);
            $this->postRepository->add($post);

            /** @var User $user */
            $user = $this->getUser();
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
        /** @var Post $post */
        $post = $this->postRepository->findOneById($id);
        $this->denyAccessUnlessGranted('edit', $post);

        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->postRepository->add($post);

            $this->logger->info(
                sprintf(
                    'The user %s edited the article: %s.',
                    $this->getUser()->getUserIdentifier(),
                    $post->getId()
                )
            );

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
        /** @var Post $post */
        $post = $this->postRepository->findOneById($id);
        $this->denyAccessUnlessGranted('delete', $post);

        if ($this->isCsrfTokenValid('delete' . $post->getId(), $request->request->get('_token'))) {
            $this->logger->warning(
                sprintf(
                    'The user %s deleted the article: %s.',
                    $this->getUser()->getUserIdentifier(),
                    $post->getId()
                )
            );

            $this->postRepository->remove($post);
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
