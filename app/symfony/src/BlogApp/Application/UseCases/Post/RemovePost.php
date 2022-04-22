<?php

namespace App\BlogApp\Application\UseCases\Post;

use App\BlogApp\Domain\Entity\Post;
use App\BlogApp\Domain\LoggerInterface;
use App\BlogApp\Domain\PostRepositoryInterface;

class RemovePost
{
    private PostRepositoryInterface $postRepository;
    private LoggerInterface $logger;

    public function __construct(PostRepositoryInterface $postRepository, LoggerInterface $logger)
    {
        $this->postRepository = $postRepository;
        $this->logger         = $logger;
    }

    public function execute(Post $post, int $userId): void
    {
        $id = $post->getId();
        $this->postRepository->remove($post);
        $this->logger->info(
            'User deleted post',
            ['PostId' => $id, 'UserId' => $userId]
        );
    }
}