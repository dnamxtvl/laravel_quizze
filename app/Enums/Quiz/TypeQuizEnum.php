<?php

namespace App\Enums\Quiz;

enum TypeQuizEnum: int
{
    case ALL = 1;
    case SHARE_WITH_ME = 2;
    case CREATED_BY_ME = 3;
}
