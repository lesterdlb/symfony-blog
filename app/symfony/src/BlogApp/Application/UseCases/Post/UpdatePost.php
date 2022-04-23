<?php

declare(strict_types=1);

namespace App\BlogApp\Application\UseCases\Post;

use App\BlogApp\Domain\Entity\Post;
use App\BlogApp\Domain\LoggerInterface;
use App\BlogApp\Domain\PostRepositoryInterface;

class UpdatePost
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
        $this->postRepository->add($post);
        $this->logger->info(
            'User edited a post',
            ['PostId' => $post->getId(), 'UserId' => $userId]
        );
    }
}