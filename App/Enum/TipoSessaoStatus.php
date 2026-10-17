<?php

declare(strict_types=1);

namespace App\Enum;

enum TipoSessaoStatus: string
{
    case AGENDADA = 'AGENDADA';
    case REALIZADA = 'REALIZADA';
    case CANCELADA_PACIENTE = 'CANCELADA_PACIENTE';
    case CANCELADA_PSICOLOGO = 'CANCELADA_PSICOLOGO';
    case REMARCADA = 'REMARCADA';
}