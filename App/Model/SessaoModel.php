<?php

declare(strict_types=1);

namespace App\Model;

use App\Enum\TipoSessaoStatus;
use DateTimeImmutable;
use DomainException;

class SessaoModel
{
    public function __construct(
        private ?int $id,
        private int $pacienteId,
        private int $psicologoId,
        private int $vendaOrigemId,
        private DateTimeImmutable $dataAgendada,
        private DateTimeImmutable $horaAgendada,
        private TipoSessaoStatus $status,
        private ?int $loteRepasseId = null
    ) {}

    public function marcarComoRealizada(): void
    {
        if ($this->status === TipoSessaoStatus::CANCELADA_PACIENTE || $this->status === TipoSessaoStatus::CANCELADA_PSICOLOGO) {
            throw new DomainException("Não é possível realizar uma sessão que já foi cancelada.");
        }

        $this->status = TipoSessaoStatus::REALIZADA;
    }

    public function remarcar(DateTimeImmutable $novaData, DateTimeImmutable $novaHora): void
    {
        if ($this->status === TipoSessaoStatus::REALIZADA) {
            throw new DomainException("Sessões já realizadas não podem ser remarcadas.");
        }

        if ($this->status === TipoSessaoStatus::CANCELADA_PACIENTE || $this->status === TipoSessaoStatus::CANCELADA_PSICOLOGO) {
            throw new DomainException("Sessões canceladas não podem ser remarcadas. Crie uma nova sessão.");
        }

        $this->dataAgendada = $novaData;
        $this->horaAgendada = $novaHora;
        $this->status = TipoSessaoStatus::REMARCADA;
    }

    private function validarCancelamento(): void
    {
        if ($this->status === TipoSessaoStatus::REALIZADA) {
            throw new DomainException(
                "Não é possível cancelar uma sessão que já foi realizada."
            );
        }

        if (
            $this->status === TipoSessaoStatus::CANCELADA_PACIENTE
            || $this->status === TipoSessaoStatus::CANCELADA_PSICOLOGO
        ) {
            throw new DomainException(
                "Não é possível cancelar uma sessão que já foi cancelada."
            );
        }

        if ($this->loteRepasseId !== null) {
            throw new DomainException(
                "Sessão já repassada ao financeiro do psicólogo não pode ser cancelada."
            );
        }
    }

    public function cancelarPeloPaciente(): void
    {
        $this->validarCancelamento();
        $this->status = TipoSessaoStatus::CANCELADA_PACIENTE;
    }

    public function cancelarPeloPsicologo(): void
    {
        $this->validarCancelamento();
        $this->status = TipoSessaoStatus::CANCELADA_PSICOLOGO;
    }
    
    // ==========================================
    // MÉTODOS GETTERS
    // ==========================================

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPacienteId(): int
    {
        return $this->pacienteId;
    }

    public function getPsicologoId(): int
    {
        return $this->psicologoId;
    }

    public function getVendaOrigemId(): int
    {
        return $this->vendaOrigemId;
    }

    public function getDataAgendada(): DateTimeImmutable
    {
        return $this->dataAgendada;
    }

    public function getHoraAgendada(): DateTimeImmutable
    {
        return $this->horaAgendada;
    }

    public function getStatusSessao(): TipoSessaoStatus
    {
        return $this->status;
    }

    public function getLoteRepasseId(): ?int
    {
        return $this->loteRepasseId;
    }
}
