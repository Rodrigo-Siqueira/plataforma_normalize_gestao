<?php

declare(strict_types=1);

namespace App\Model;

use App\Enum\TipoFormaPagamento;
use App\Enum\TipoVendaStatus;
use DateTimeImmutable;
use DomainException;

class VendaModel
{
    public function __construct(
        private ?int $id,
        private int $pacienteId,
        private int $responsavelId,
        private int $produtoId,
        private float $valorCobrado,
        private ?DateTimeImmutable $dataVenda,
        private ?DateTimeImmutable $dataPagamento,
        private TipoFormaPagamento $formaPagamento,
        private TipoVendaStatus $status
    ) {
        $this->validar();
    }

    private function validar(): void
    {
        if ($this->valorCobrado <= 0) {
            throw new DomainException("O valor cobrado da venda deve ser maior que zero.");
        }

        if ($this->status === TipoVendaStatus::PAGO && $this->dataPagamento === null) {
            throw new DomainException("Uma venda com status PAGO deve obrigatoriamente possuir uma data de pagamento.");
        }

        if ($this->status === TipoVendaStatus::PENDENTE && $this->dataPagamento !== null) {
            throw new DomainException("Uma venda com status PENDENTE não deve possuir uma data de pagamento.");
        }
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

    public function getResponsavelId(): int
    {
        return $this->responsavelId;
    }

    public function getProdutoId(): int
    {
        return $this->produtoId;
    }

    public function getValorCobrado(): float
    {
        return $this->valorCobrado;
    }

    public function getDataVenda(): ?DateTimeImmutable
    {
        return $this->dataVenda;
    }

    public function getDataPagamento(): ?DateTimeImmutable
    {
        return $this->dataPagamento;
    }

    public function getFormaPagamento(): TipoFormaPagamento
    {
        return $this->formaPagamento;
    }

    public function getStatus(): TipoVendaStatus
    {
        return $this->status;
    }
}