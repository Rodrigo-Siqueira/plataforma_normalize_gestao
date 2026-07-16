<?php

declare(strict_types=1);

namespace App\Enum;

enum TipoStatusDespesa: string
{
    case PENDENTE = 'PENDENTE';
    case PAGO = 'PAGO';
}