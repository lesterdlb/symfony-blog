<?php

namespace App\BlogApp\Application\UseCases\Post;

use App\BlogApp\Domain\Entity\Post;
use App\BlogApp\Domain\PostRepositoryInterface;

class FindOnePostById
{
    private PostRepositoryInterface $postRepository;

    public function __construct(PostRepositoryInterface $postRepository)
    {
        $this->postRepository = $postRepository;
    }

    public function execute(int $id): Post
    {
        return $this->postRepository->findOneById($id);
    }
}