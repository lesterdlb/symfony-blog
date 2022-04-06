<?php

namespace App\Config;

enum States: string
{
    case Draft = 'DRAFT';
    case Published = 'PUBLISHED';
    case Rejected = 'REJECTED';
}