<?php

namespace Climb\Grades\Domain\Service;

enum PrimaryIndexPolicy: string
{
    case LOWEST = 'lowest';
    case MIDDLE = 'middle';
    case HIGHEST = 'highest';
}
