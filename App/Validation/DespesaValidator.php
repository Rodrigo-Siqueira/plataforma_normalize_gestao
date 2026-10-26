<?php

declare(strict_types=1);

namespace App\Validation;

use App\Dto\Input\DespesaInputDTO;
use App\Enum\TipoStatusDespesa;

class DespesaValidator
{
    private array $erros = [];

    public function validar(DespesaInputDTO $dto): array
    {
        $this->erros = [];
        
        // Validação de DESCRIÇÃO
        if (empty($dto->descricao)) {
            $this->erros['descricao'] = 'A descrição da despesa é obrigatória.';
        } elseif (mb_strlen($dto->descricao) < 3) {
            $this->erros['descricao'] = 'A descrição deve ter no mínimo 3 caracteres.';
        } elseif (mb_strlen($dto->descricao) > 100) {
            $this->erros['descricao'] = 'A descrição não pode ultrapassar 100 caracteres.';
        }

        // Validação TIPO
        if ($dto->tipo === null) {
            $this->erros['tipo'] = 'O tipo de despesa selecionado é inválido.';
        }

        // Validação da CATEGORIA
        if ($dto->categoria === null) {
            $this->erros['categoria'] = 'A categoria selecionada é inválida.';
        }

        // Validação VALOR
        if ($dto->valor <= 0) {
            $this->erros['valor'] = 'O valor da despesa deve ser maior que zero.';
        } elseif ($dto->valor > 15000) {
            $this->erros['valor'] = 'O valor da despesa ultrapassa o limite permitido de R$ 15.000,00.';
        }

        // Validação do STATUS
        if ($dto->status === null) {
            $this->erros['status'] = 'O status da despesa é inválido.';
        }

        if ($dto->dataDespesa === null) {
            $this->erros['dataDespesa'] = 'A data da despesa informada é inválida.';
        }

        // Validação de DATAS CRUZADAS (Agora usando null em vez de string vazia)
        if ($dto->dataDespesa !== null && $dto->dataPagamento !== null && $dto->dataPagamento < $dto->dataDespesa) {
            $this->erros['dataPagamento'] = 'A data de pagamento não pode ser anterior à data de emissão da despesa.';
        }

        if ($dto->status === TipoStatusDespesa::PAGO && $dto->dataPagamento === null) {
            $this->erros['dataPagamento'] = 'Uma despesa com status PAGO deve obrigatoriamente possuir uma data de pagamento.';
        }

        if ($dto->status !== TipoStatusDespesa::PAGO && $dto->dataPagamento !== null) {
            $this->erros['dataPagamento'] = 'Uma despesa pendente ou cancelada não deve possuir uma data de pagamento vinculada.';
        }

        return $this->erros;
    }
}
