<?php

namespace App\Enum;

enum ErrorCode: int
{
    case PAGINATION_BAD_PAGE = 4001;
    case PAGINATION_BAD_COUNT = 4002;
}
