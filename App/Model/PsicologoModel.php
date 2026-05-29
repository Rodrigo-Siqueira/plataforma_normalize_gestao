<?php

declare(strict_types=1);

namespace App\Model;

use DomainException;

class PsicologoModel
{
    public function __construct(
        private ?int $id,
        private ?int $pessoaId,
        private string $crp,
        private float $comissao,
        private string $dataInicio,
        private int $ativo = 1
    ) {
        $this->validar();
    }

    private function validar(): void
    {
        $this->validarDataInicio();
        $this->validarCRP();
        $this->validarComissao();
    }

    private function validarComissao(): void
    {
        if ($this->comissao > 0.5) {
            throw new DomainException('A comissão não pode ultrapassar 50%');
        }
    }

    private function validarCRP(): void
    {
        if (mb_strlen($this->crp) < 3 || !preg_match('/^\d{1,15}$/', $this->crp)) {
            throw new DomainException('O CRP deve ser maior que 2 caracteres e máximo 15 caracteres.');
        }
    }

    private function validarDataInicio(): void
    {
        if (strtotime($this->dataInicio) > strtotime(date('Y-m-d'))) {
            throw new DomainException('A data não pode ser maior que a data de hoje.');
        }
    }

    public function alterarComissao(float $comissao): void
    {
        $this->comissao = $comissao;
        $this->validarComissao();
    }

    public function alterarCRP(string $crp): void
    {
        $this->crp = trim($crp);
        $this->validarCRP();
    }
    public function iniciouEM(string $dataInicio): void
    {
        $this->dataInicio = trim($dataInicio);
        $this->validarDataInicio();
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

    public function getCRP(): string
    {
        return $this->crp;
    }

    public function getComissao(): float
    {
        return $this->comissao;
    }

    public function getDataInicio(): string
    {
        return $this->dataInicio;
    }

    public function isAtivo(): int
    {
        return $this->ativo;
    }
}
