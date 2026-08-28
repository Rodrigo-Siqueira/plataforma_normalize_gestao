<?php

declare(strict_types=1);

namespace App\Enum;

enum TipoFormaPagamento: string
{
    case PIX = 'PIX';
    case CARTAO_CREDITO = 'CARTAO_CREDITO';
}