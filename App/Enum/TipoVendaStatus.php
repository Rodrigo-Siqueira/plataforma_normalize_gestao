<?php

declare(strict_types=1);

namespace App\Enum;

enum TipoVendaStatus: string
{
    case PENDENTE = 'PENDENTE';
    case PAGO = 'PAGO';
    case ATRASADO = 'ATRASADO';
    case CANCELADO = 'CANCELADO';
}
