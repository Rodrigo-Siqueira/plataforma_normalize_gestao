<?php

declare(strict_types=1);

namespace App\Validation;

use App\Dto\Input\VendaInputDTO;

class VendaValidator
{
    public function validar(VendaInputDTO $dto): array
    {
        $erros =  [];

        if ($dto->pacienteId <= 0) {
            $erros['pacienteId'] = "Selecione um Paciente.";
        }

        if ($dto->responsavelId <= 0) {
            $erros['responsavelId'] = "Selecione um Responsável financeiro.";
        }

        if ($dto->produtoId <= 0) {
            $erros['produtoId'] = "Selecione um Produto.";
        }

        if ($dto->valorCobrado <= 0) {
            $erros['valorCobrado'] = "O valor da venda deve ser maior que zero.";
        }

        if ($dto->dataVenda  == null) {
            $erros['dataVenda'] = "Data da venda inválida.";
        }

        return $erros;
    }
}