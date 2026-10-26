<?php

declare(strict_types=1);

namespace App\Dto\Output;

use App\Enum\TipoVendaStatus;
use DateTimeImmutable;
use Normalize\Helper\Helper;

readonly class VendaOutputDTO
{
    public function __construct(
        public ?int $id,
        public string $pacienteNome,
        public string $produtoNome,
        public float $valorCobrado,
        public string $valorFormatado,
        public ?DateTimeImmutable $dataVenda,
        public ?DateTimeImmutable $dataPagamento,
        public TipoVendaStatus $status
    ) {}

    public static function criarDeEntidade(object $dados): self
    {
        return new self(
            id: (int) $dados->id,
            pacienteNome: (string) $dados->pacienteNome,
            produtoNome: (string) $dados->produtoNome,
            valorCobrado: (float) $dados->valorCobrado,
            valorFormatado: (string) Helper::formatarMoeda((float) $dados->valorCobrado),
            dataVenda: $dados->dataVenda ? new DateTimeImmutable((string) $dados->dataVenda) : null,
            dataPagamento: $dados->dataPagamento ? new DateTimeImmutable((string) $dados->dataPagamento) : null,
            status: TipoVendaStatus::from($dados->statusPagamento)
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
