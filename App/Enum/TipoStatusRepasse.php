<?php

declare(strict_types=1);

namespace App\Enum;

enum TipoStatusRepasse: string
{
    case ABERTO = 'ABERTO';
    case PAGO = 'PAGO';
}
