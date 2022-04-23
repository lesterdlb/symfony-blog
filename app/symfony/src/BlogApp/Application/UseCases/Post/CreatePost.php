<?php

declare(strict_types=1);

namespace App\BlogApp\Application\UseCases\Post;

use App\BlogApp\Domain\Entity\Post;
use App\BlogApp\Domain\Entity\User;
use App\BlogApp\Domain\LoggerInterface;
use App\BlogApp\Domain\PostRepositoryInterface;

class CreatePost
{
    private PostRepositoryInterface $postRepository;
    private LoggerInterface $logger;

    public function __construct(PostRepositoryInterface $postRepository, LoggerInterface $logger)
    {
        $this->postRepository = $postRepository;
        $this->logger         = $logger;
    }

    public function execute(string $title, string $content, string $status, User $user): void
    {
        $post = new Post();
        $post->setDate(new \DateTime());
        $post->setContent($content);
        $post->setTitle($title);
        $post->setUser($user);
        $post->setStatus($status);

        $this->postRepository->add($post);

        $this->logger->info(
            'User created new post',
            ['PostId' => $post->getId(), 'UserId' => $user->getId()]
        );
    }
}