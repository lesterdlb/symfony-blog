<?php

namespace App\BlogApp\Domain;

use App\BlogApp\Domain\Entity\Post;
use Pagerfanta\Pagerfanta;

/**
 * @method findOneById(int $id)
 */
interface PostRepositoryInterface
{
    public function add(Post $entity, bool $flush = true): void;

    public function remove(Post $entity, bool $flush = true): void;

    public function findByValue(null|string $value, null|string $name, int $pageSize, int $currentPage): Pagerfanta;
}