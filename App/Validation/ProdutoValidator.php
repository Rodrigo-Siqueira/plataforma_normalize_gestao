<?php

declare(strict_types=1);

namespace App\Validation;

use App\Dto\Input\ProdutoInputDTO;

class ProdutoValidator
{
    public function validar(ProdutoInputDTO $dto): array
    {
        $erros = [];

        // Validação de NOME
        if (empty($dto->nome)) {
            $erros['nome'] = 'O nome do produto é obrigatório.';
        } elseif (mb_strlen($dto->nome) < 3) {
            $erros['nome'] = 'O nome deve ter no mínimo 3 caracteres.';
        } elseif (mb_strlen($dto->nome) > 50) {
            $erros['nome'] = 'O nome não pode ultrapassar 50 caracteres.';
        } elseif (!preg_match('/^[\p{L}\s]+$/u', $dto->nome)) {
            $erros['nome'] = 'O nome deve conter apenas letras e espaços.';
        }

        // Validação de TIPO
        if (!in_array($dto->tipo, ['PACOTE', 'AVULSO'])) {
            $erros['tipo'] = 'O tipo de produto é inválido.';
        }

        // Validação de ATIVO
        if (!in_array($dto->ativo, [0, 1], true)) {
            $erros['ativo'] = 'Status de atividade inválido.';
        }

        // Validação de PREÇO
        if ($dto->preco <= 0) {
            $erros['preco'] = 'O preço deve ser maior que zero.';
        }

        // Validação de SESSÕES
        if ($dto->numeroSessoes < 1) {
            $erros['numeroSessoes'] = 'A quantidade deve ser no mínimo 1.';
        }

        return $erros;
    }
}
