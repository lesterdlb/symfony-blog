<?php

namespace App\BlogApp\Application\Config;

enum PostStatus: string
{
    case Draft = 'DRAFT';
    case Published = 'PUBLISHED';
    case Rejected = 'REJECTED';
}