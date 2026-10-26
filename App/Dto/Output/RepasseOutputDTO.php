<?php

declare(strict_types=1);

namespace App\Dto\Output;

use App\Enum\TipoStatusRepasse;
use DateTimeImmutable;

readonly class RepasseOutputDTO
{
    public function __construct(
        public int $id,
        public int $psicologoId,
        public ?string $psicologoNome,
        public DateTimeImmutable $periodoInicial,
        public DateTimeImmutable $periodoFinal,
        public float $valorLiquido,
        public TipoStatusRepasse $status,
        public ?DateTimeImmutable $dataPagamento,
        public DateTimeImmutable $dataGeracao
    ) {}

    public static function criarDeEntidade(object $dados): self
    {
        return new self(
            id: (int) $dados->id,
            psicologoId: (int) $dados->psicologo_id,
            psicologoNome: isset($dados->psicologo_nome)
                ? (string) $dados->psicologo_nome
                : null,
            periodoInicial: new DateTimeImmutable((string) $dados->periodo_inicio),
            periodoFinal: new DateTimeImmutable((string) $dados->periodo_fim),
            valorLiquido: (float) $dados->valor_liquido_pago,
            status: $dados->status instanceof TipoStatusRepasse
                ? $dados->status
                : TipoStatusRepasse::from(
                    (string) $dados->status
                ),
            dataPagamento: isset($dados->data_pagamento)
                && $dados->data_pagamento !== null
                ? new DateTimeImmutable(
                    (string) $dados->data_pagamento
                )
                : null,
            dataGeracao: new DateTimeImmutable((string) $dados->data_geracao)
        );
    }

    /**
     * @param array<int, object> $registros
     * @return array<int, self>
     */
    public static function criarColecao(array $registros): array
    {
        return array_map(
            static fn(object $dados): self =>
            self::criarDeEntidade($dados), $registros
        );
    }
}
