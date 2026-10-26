<?php

declare(strict_types=1);

namespace App\Dto\Input;

use App\Enum\TipoFormaPagamento;
use App\Enum\TipoVendaStatus;
use DateTimeImmutable;

readonly class VendaInputDTO
{
    public function __construct(
        public ?int $id,
        public int $pacienteId,
        public int $responsavelId,
        public int $produtoId,
        public float $valorCobrado,
        public ?DateTimeImmutable $dataVenda,
        public ?DateTimeImmutable $dataPagamento,
        public TipoFormaPagamento $formaPagamento,
        public TipoVendaStatus $status
    ){}

    public static function criarDeArray(array $dados): self
    {
        $valorLimpo = str_replace(['.', ','], ['', '.'], (string) $dados['valorCobrado']);

        return new self(
            id: isset($dados['id']) && $dados['id'] !== '' ? (int) $dados['id'] : null,
            pacienteId: (int) $dados['pacienteId'],
            responsavelId: (int) $dados['responsavelPessoaId'],
            produtoId: (int) $dados['produtoId'],
            valorCobrado: (float) $valorLimpo,
            dataVenda: !empty($dados['dataVenda']) ? new DateTimeImmutable($dados['dataVenda']) : null,
            dataPagamento: !empty($dados['dataPagamento']) ? new DateTimeImmutable($dados['dataPagamento']) : null,
            formaPagamento: TipoFormaPagamento::from($dados['formaPagamento']),
            status: TipoVendaStatus::from($dados['status'])
        );
    }
}
