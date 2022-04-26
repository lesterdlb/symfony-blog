<?php

declare(strict_types=1);

namespace App\BlogApp\Application\Config;

enum PostStatus: string
{
    case Draft = 'DRAFT';
    case Published = 'PUBLISHED';
    case Rejected = 'REJECTED';
}