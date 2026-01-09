<?php

namespace App\Dto\Input;

class ListInput
{
    public function __construct(public int $page, public int $count)
    {
    }
}
