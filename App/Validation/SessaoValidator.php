<?php

declare(strict_types=1);

namespace App\Validation;

use App\Dto\Input\SessaoInputDTO;

class SessaoValidator
{
    public function validar(SessaoInputDTO $dto): array
    {
        $erros =  [];

        if ($dto->pacienteId <= 0) {
            $erros['pacienteId'] = "Selecione um Paciente.";
        }

        if ($dto->psicologoId <= 0) {
            $erros['psicologoId'] = "Selecione um Psicólogo responsável.";
        }

        if ($dto->dataAgendada  == null) {
            $erros['dataAgendada'] = "Data do agendamento inválida.";
        }
        if ($dto->horaAgendada  == null) {
            $erros['horaAgendada'] = "Hora do agendamento inválida.";
        }

        return $erros;
    }
}