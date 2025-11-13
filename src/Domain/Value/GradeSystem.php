<?php

namespace Climb\Grades\Domain\Value;

enum GradeSystem: string
{
    case FR = 'FR';             // French Sport Scale
    case UIAA = 'UIAA';         // International Scale / UIAA Scale of Difficulty
    case YDS = 'YDS';           // American Yosemite Decimal System
    case UK_tech = 'UK-tech';   // British Technical
    case UK_adj = 'UK-adj';     // British Adjectival
    case SAXON = 'SAXON';       // German/Swiss Saxon scale
    case AU = 'AU';             // Ewbank Australian
    case SA = 'SA';             // Ewbank South African
    case FIN = 'FIN';           // Scandinavian Finland
    case NO = 'NO';             // Scandinavian Norway
    case BR = 'BR';             // Brazilian Technical
    case PO = 'PO';             // Polish Cracow/Kurtyka
    case V = 'V';               // American Verm/Hueco V-Grade
    case FONT = 'FONT';         // French Fontainebleau Scale
}
