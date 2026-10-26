<?php

declare(strict_types=1);

namespace App\Dto\Output;

use App\Enum\TipoSessaoStatus;
use DateTimeImmutable;

readonly class SessaoOutputDTO
{
    public function __construct(
        public int $id,
        public ?int $pacienteId,
        public ?int $psicologoId,
        public ?string $pacienteNome,
        public ?string $psicologoNome,
        public ?int $vendaOrigemId,
        public ?int $loteRepasseId,
        public DateTimeImmutable $dataAgendada,
        public DateTimeImmutable $horaAgendada,
        public TipoSessaoStatus $status,
        public ?string $carimbo = null
    ) {}

    public static function criarDeEntidade(object $dados): self
    {
        return new self(
            id: (int) $dados->id,
            pacienteId: isset($dados->paciente_id) ? (int) $dados->paciente_id : null,
            psicologoId: isset($dados->psicologo_id) ? (int) $dados->psicologo_id : null,
            pacienteNome: isset($dados->paciente) ? (string) $dados->paciente : null,
            psicologoNome: isset($dados->psicologo) ? (string) $dados->psicologo : null,
            vendaOrigemId: isset($dados->venda_origem_id) ? (int) $dados->venda_origem_id : null,
            loteRepasseId: isset($dados->lote_repasse_id) ? (int) $dados->lote_repasse_id : null,
            dataAgendada: new DateTimeImmutable($dados->data_agendada),
            horaAgendada: new DateTimeImmutable($dados->hora_agendada),
            status: is_string($dados->status) ? TipoSessaoStatus::from($dados->status) : $dados->status,
            carimbo: $dados->carimbo ?? null
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
