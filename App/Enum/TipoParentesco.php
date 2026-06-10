<?php

declare(strict_types=1);

namespace App\Enum;

enum TipoParentesco: string
{
    case PROPRIO = 'PROPRIO';
    case OUTRO = 'OUTRO';
    case PAI = 'PAI';
    case MAE = 'MAE';
}