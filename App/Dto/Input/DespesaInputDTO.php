<?php

declare(strict_types=1);

namespace App\Dto\Input;

use App\Enum\TipoCategoriaDespesa;
use App\Enum\TipoDespesa;
use App\Enum\TipoStatusDespesa;
use DateTimeImmutable;

readonly class DespesaInputDTO
{
    public function __construct(
        public ?int $id,
        public string $descricao,
        public ?TipoDespesa $tipo,
        public ?TipoCategoriaDespesa $categoria,
        public float $valor,
        public ?DateTimeImmutable $dataDespesa,
        public ?DateTimeImmutable $dataPagamento,
        public ?TipoStatusDespesa $status
    ) {}

    public static function criarDeArray(array $dados): self
    {
        $dataDespesa = !empty($dados['dataDespesa']) ? trim($dados['dataDespesa']) : date('Y-m-d');

        return new self(
            id: isset($dados['id']) && $dados['id'] !== '' ? (int) $dados['id'] : null,
            descricao: trim($dados['descricao'] ?? ''),
            tipo: TipoDespesa::tryFrom($dados['tipo'] ?? ''),
            categoria: TipoCategoriaDespesa::tryFrom($dados['categoria'] ?? ''),
            valor: (float) ($dados['valor'] ?? 0.0),
            dataDespesa: self::filtrarData($dataDespesa),
            dataPagamento: !empty($dados['dataPagamento']) ? self::filtrarData(trim($dados['dataPagamento'])) : null,
            status: TipoStatusDespesa::tryFrom($dados['status'] ?? '')
        );
    }

    private static function filtrarData(string $dataStr): ?DateTimeImmutable
    {
        $data = DateTimeImmutable::createFromFormat('Y-m-d', $dataStr);
        $erros = DateTimeImmutable::getLastErrors();

        if ($data === false || ($erros !== false && ($erros['warning_count'] > 0 || $erros['error_count'] > 0))) {
            return null;
        }
        return $data;
    }
}
