<?php

namespace App\BlogApp\Application\Post;

use App\BlogApp\Domain\PostRepositoryInterface;
use Pagerfanta\Pagerfanta;

class GetPosts
{
    private PostRepositoryInterface $postRepository;

    public function __construct(PostRepositoryInterface $postRepository)
    {
        $this->postRepository = $postRepository;
    }

    public function execute(null|string $value, null|string $name, int $pageSize, int $currentPage ): Pagerfanta
    {
        return $this->postRepository->findByValue($value, $name, $pageSize, $currentPage);
    }
}