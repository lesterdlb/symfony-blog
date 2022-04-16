<?php

namespace App\BlogApp\Application\Post;

use App\BlogApp\Domain\Entity\Post;
use App\BlogApp\Domain\PostRepositoryInterface;

class CreatePost
{
    private PostRepositoryInterface $postRepository;

    public function __construct(PostRepositoryInterface $postRepository)
    {
        $this->postRepository = $postRepository;
    }

    public function execute(Post $post): void
    {
        $this->postRepository->add($post);
    }
}