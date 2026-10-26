<?php

declare(strict_types=1);

namespace App\Model;

use App\Enum\TipoParentesco;
use DomainException;

class ResponsavelPacienteModel
{
    public function __construct(
        private ?int $id,
        private ?int $pessoaId,
        private int $pacienteId,
        private TipoParentesco $parentesco
    ) {
        $this->validar();
    }

    private function validar(): void
    {
        $this->validarPaciente();
    }

    private function validarPaciente() : void {
        if ($this->pacienteId <= 0) {
            throw new DomainException('Responsável precisa ter um paciente.');
        }
    }

    public function alterarPaciente(int $pacienteId) : void {
        $this->pacienteId = $pacienteId;
        $this->validarPaciente();
    }

    public function alterarParentesco(TipoParentesco $parentesco) : void {
        $this->parentesco = $parentesco;
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

    public function getPacienteId(): int
    {
        return $this->pacienteId;
    }

    public function getParentesco(): string
    {
        return $this->parentesco->value;
    }
}
