<?php

declare(strict_types=1);

namespace App\Enum;

enum TipoDespesa: string
{
    case FIXA = 'FIXA';
    case VARIAVEL = 'VARIAVEL';
}