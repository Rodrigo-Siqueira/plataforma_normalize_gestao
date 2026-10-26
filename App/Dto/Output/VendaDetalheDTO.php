<?php

declare(strict_types=1);

namespace App\Dto\Output;

use App\Enum\TipoFormaPagamento;
use App\Enum\TipoVendaStatus;
use DateTimeImmutable;

readonly class VendaDetalheDTO
{
    public function __construct(
        public ?int $id,
        public int $pacienteId,
        public int $responsavelPessoaId,
        public int $produtoId,
        public float $valorCobrado,
        public ?DateTimeImmutable $dataVenda,
        public ?DateTimeImmutable $dataPagamento,
        public TipoFormaPagamento $formaPagamento,
        public TipoVendaStatus $status
    ) {}

    public static function criarDeEntidade(object $dados): self
    {
        return new self(
            id: (int) $dados->id,
            pacienteId: (int) $dados->paciente_id,
            responsavelPessoaId: (int) $dados->responsavel_pessoa_id,
            produtoId: (int) $dados->produto_id,
            valorCobrado: (float) $dados->valor_cobrado,
            dataVenda: $dados->data_venda ? new DateTimeImmutable((string) $dados->data_venda) : null,
            dataPagamento: $dados->data_pagamento ? new DateTimeImmutable((string) $dados->data_pagamento) : null,
            formaPagamento: TipoFormaPagamento::from($dados->forma_pagamento),
            status: TipoVendaStatus::from($dados->status_pagamento)
        );
    }
}