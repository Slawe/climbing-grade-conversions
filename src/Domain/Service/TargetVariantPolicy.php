<?php

namespace Climb\Grades\Domain\Service;

enum TargetVariantPolicy: string
{
    case FIRST = 'first';
    case MIDDLE = 'middle';
    case LAST = 'last';
}
