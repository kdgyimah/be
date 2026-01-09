<?php

namespace App\Enum;

enum ConstructionProjectState: string
{
    case STARTING = 'STARTING';
    case RUNNING = 'RUNNING';
    case FINISHED = 'FINISHED';
    case CANCELED = 'CANCELED';
}
