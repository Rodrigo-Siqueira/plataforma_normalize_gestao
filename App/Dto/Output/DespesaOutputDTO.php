<?php

declare(strict_types=1);

namespace App\Dto\Output;

use App\Enum\TipoCategoriaDespesa;
use App\Enum\TipoDespesa;
use App\Enum\TipoStatusDespesa;
use DateTimeImmutable;
use Normalize\Helper\Helper;

readonly class DespesaOutputDTO
{
    public function __construct(
        public ?int $id,
        public string $descricao,
        public TipoDespesa $tipo,
        public TipoCategoriaDespesa $categoria,
        public float $valor,
        public string $valorFormatado,
        public DateTimeImmutable $dataDespesa,
        public ?DateTimeImmutable $dataPagamento,
        public TipoStatusDespesa $status
    ) {}

    public static function criarDeEntidade(object $dados): self
    {
        return new self(
            id: (int) $dados->id,
            descricao: (string) $dados->descricao,
            tipo: TipoDespesa::from($dados->tipo),
            categoria: TipoCategoriaDespesa::from($dados->categoria),
            valor: (float) $dados->valor,
            valorFormatado: (string) Helper::formatarMoeda((float) $dados->valor),
            dataDespesa: new DateTimeImmutable((string) $dados->data_competencia),
            dataPagamento: $dados->data_pagamento ? new DateTimeImmutable((string) $dados->data_pagamento) : null,
            status: TipoStatusDespesa::from($dados->status)
        );
    }

    public static function criarColecao(array $listaDeDados): array
    {
        return array_map(
            fn(object $dados) => self::criarDeEntidade($dados),
            $listaDeDados
        );
    }
}
