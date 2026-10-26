<?php

declare(strict_types=1);

namespace App\Enum;

enum TipoCategoriaDespesa: string
{
    case MARKETING = 'MARKETING';
    case SOFTWARE = 'SOFTWARE';
    case IMPOSTOS = 'IMPOSTOS';
    case CONTABILIDADE ='CONTABILIDADE';
    case INTERNET = 'INTERNET';
    case GOOGLE_ADS = 'GOOGLE ADS';
}