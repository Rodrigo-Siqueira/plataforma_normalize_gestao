<?php

declare(strict_types=1);

namespace App\Service;

use App\Dto\Input\DespesaInputDTO;
use App\Model\DespesaModel;
use App\Repository\DespesaRepository;
use InvalidArgumentException;

class DespesaService
{
    private DespesaRepository $repositorio;

    public function __construct()
    {
        $this->repositorio = new DespesaRepository();
    }

    public function salvar(DespesaInputDTO $dto): int
    {
        $despesa = new DespesaModel(
            id: $dto->id,
            descricao: $dto->descricao,
            tipo: $dto->tipo ?? throw new InvalidArgumentException('O Tipo da despesa é inválido ou não foi validado.'),
            categoria: $dto->categoria ?? throw new InvalidArgumentException('A Categoria é inválida ou não foi validada.'),
            valor: $dto->valor,
            dataDespesa: $dto->dataDespesa ?? throw new InvalidArgumentException('A Data da despesa é inválida ou não foi validada.'),
            dataPagamento: $dto->dataPagamento,
            status: $dto->status ?? throw new InvalidArgumentException('O Status é inválido ou não foi validado.')
        );

        if ($despesa->getId() === null) {
            return $this->repositorio->salvar($despesa);
        } else {
            $this->repositorio->alterar($despesa);
            return (int) $despesa->getId();
        }
    }
}