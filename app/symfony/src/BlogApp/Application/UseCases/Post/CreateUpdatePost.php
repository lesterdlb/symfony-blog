<?php

namespace App\BlogApp\Application\UseCases\Post;

use App\BlogApp\Domain\Entity\Post;
use App\BlogApp\Domain\PostRepositoryInterface;

class CreateUpdatePost
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