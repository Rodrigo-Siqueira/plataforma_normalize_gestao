<?php

declare(strict_types=1);

namespace App\Model;

use DomainException;

class PacienteModel
{
    public function __construct(
        private ?int $id,
        private ?int $pessoaId,
        private int $psicologoResponsavelId,
        private int $ativo = 1
    ) {
        $this->validar();
    }

    private function validar(): void
    {
        $this->validarPsicologoResponsavel();
    }

    private function validarPsicologoResponsavel(): void
    {
        if ($this->psicologoResponsavelId <= 0) {
            throw new DomainException('Paciente precisa ter um psicologo responsável');
        }
    }

    public function alterarPsicologoResponsavel(int $psicologoResponsavelId): void
    {
        $this->psicologoResponsavelId = $psicologoResponsavelId;
        $this->validarPsicologoResponsavel();
    }

    public function ativar(): void
    {
        $this->ativo = 1;
    }

    public function desativar(): void
    {
        $this->ativo = 0;
    }

    // ==========================================
    // MÉTODOS GETERS
    // ==========================================

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPessoaId(): ?int
    {
        return $this->pessoaId;
    }

    public function getPsicologoResponsavelId(): int
    {
        return $this->psicologoResponsavelId;
    }

    public function isAtivo(): int
    {
        return $this->ativo;
    }
}
