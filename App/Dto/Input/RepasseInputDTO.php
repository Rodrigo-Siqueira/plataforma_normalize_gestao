<?php

declare(strict_types=1);

namespace App\Dto\Input;

use App\Enum\TipoStatusRepasse;
use DateTimeImmutable;

readonly class RepasseInputDTO
{
    public function __construct(
        public ?int $id,
        public int $psicologoId,
        public DateTimeImmutable $periodoInicial,
        public DateTimeImmutable $periodoFinal,
        public ?float $valorLiquido,
        public TipoStatusRepasse $status,
        public ?DateTimeImmutable $dataPagamento
    ) {}

    public static function criarDeArray(array $dados): self
    {
        $id = isset($dados['id']) && $dados['id'] !== '' ? (int) $dados['id'] : null;

        $valorLiquido = isset($dados['valorLiquido'])
            && $dados['valorLiquido'] !== ''
            ? self::converterValorMonetario(
                (string) $dados['valorLiquido']
            )
            : null;

        $status = TipoStatusRepasse::tryFrom(
            (string) ($dados['status'] ?? '')
        ) ?? TipoStatusRepasse::ABERTO;

        $dataPagamento = isset($dados['dataPagamento'])
            && $dados['dataPagamento'] !== ''
            ? new DateTimeImmutable(
                (string) $dados['dataPagamento']
            ) : null;

        return new self(
            id: $id,
            psicologoId: (int) ($dados['psicologoId'] ?? 0),
            periodoInicial: new DateTimeImmutable(
                (string) ($dados['periodoInicial'] ?? '')
            ),
            periodoFinal: new DateTimeImmutable(
                (string) ($dados['periodoFinal'] ?? '')
            ),
            valorLiquido: $valorLiquido,
            status: $status,
            dataPagamento: $dataPagamento
        );
    }

    private static function converterValorMonetario(string $valor): float
    {
        $valor = trim($valor);
        
        if (str_contains($valor, ',')) {
            $valor = str_replace('.', '', $valor);
            $valor = str_replace(',', '.', $valor);
        }

        return (float) $valor;
    }
}
