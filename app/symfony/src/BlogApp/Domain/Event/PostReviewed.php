<?php

namespace App\BlogApp\Domain\Event;

use App\BlogApp\Domain\Entity\Post;

class PostReviewed
{
    private string $moderatorEmail;
    private Post $post;

    public function __construct(Post $post, string $moderatorEmail)
    {
        $this->post           = $post;
        $this->moderatorEmail = $moderatorEmail;
    }

    public function getPost(): Post
    {
        return $this->post;
    }

    public function getModeratorEmail(): string
    {
        return $this->moderatorEmail;
    }
}