<?php

declare(strict_types=1);

namespace App\Validation;

use App\Dto\Input\PsicologoInputDTO;

class PsicologoValidator
{
    public function validar(PsicologoInputDTO $dto): array
    {
        $erros = [];

        // Validação de NOME
        if ($dto->pessoa->nome === '') {
            $erros['nome'] = 'O nome do psicologo é obrigatório.';
        }

        // Validação de CONTATO
        if ($dto->pessoa->contato === '') {
            $erros['contato'] = 'O contato é obrigatório.';
        }

        // Validação de CRP
        if ($dto->crp === '') {
            $erros['crp'] = 'O CRP é obrigatório.';
        }

        // Validação de ATIVO
        if (!in_array($dto->ativo, [0,1], true)) {
            $erros['ativo'] = 'Status de atividade inválido.';
        }

        // Validação de COMISSAO
        if ($dto->comissao === null) {
            $erros['comissao'] = 'A comissão é obrigatória.';
        } elseif ($dto->comissao < 0) {
            $erros['comissao'] = 'A comissão não pode ser negativa.';
        }

        // Validação de DATA DE INÍCIO
        if ($dto->dataInicio === '') {
            $erros['dataInicio'] = 'A data de início é obrigatória.';
        }

        return $erros;
    }
}