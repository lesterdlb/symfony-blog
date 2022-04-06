<?php

namespace App\Config;

enum Roles: string
{
    case User = 'ROLE_USER';
    case Moderator = 'ROLE_MODERATOR';
    case Editor = 'ROLE_EDITOR';
}