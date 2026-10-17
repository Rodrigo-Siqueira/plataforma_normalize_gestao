<?php

declare(strict_types=1);

namespace App\Dto\Input;

use App\Enum\TipoSessaoStatus;
use DateTimeImmutable;

readonly class SessaoInputDTO
{
    public function __construct(
        public ?int $id,
        public int $pacienteId,
        public int $psicologoId,
        public ?int $vendaOrigemId,
        public ?DateTimeImmutable $dataAgendada,
        public ?DateTimeImmutable $horaAgendada,
        public TipoSessaoStatus $status,
        public bool $remarcarSeguintes = false
    ) {}

    public static function criarDeArray(array $dados): self
    {
        return new self(
            id: isset($dados['id']) && $dados['id'] !== '' ? (int) $dados['id'] : null,
            pacienteId: (int) $dados['pacienteId'],
            psicologoId: (int) $dados['psicologoId'],
            vendaOrigemId: isset($dados['vendaOrigemId']) && $dados['vendaOrigemId'] !== '' ? (int) $dados['vendaOrigemId'] : null,
            dataAgendada: !empty($dados['dataAgendada']) ? new DateTimeImmutable($dados['dataAgendada']) : null,
            horaAgendada: !empty($dados['horaAgendada']) ? new DateTimeImmutable($dados['horaAgendada']) : null,
            status: TipoSessaoStatus::from($dados['status']),
            remarcarSeguintes: isset($dados['remarcarSeguintes']) ? (bool) $dados['remarcarSeguintes'] : false
        );
    }
}
