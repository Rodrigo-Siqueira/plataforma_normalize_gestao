<?php

declare(strict_types=1);

namespace App\Model;

use App\Enum\TipoStatusRepasse;
use DateTimeImmutable;
use DomainException;
use InvalidArgumentException;

class RepasseModel
{
    public function __construct(
        private ?int $id,
        private int $psicologoId,
        private DateTimeImmutable $periodoInicial,
        private DateTimeImmutable $periodoFinal,
        private float $valorLiquido,
        private TipoStatusRepasse $status = TipoStatusRepasse::ABERTO,
        private ?DateTimeImmutable $dataPagamento = null,
        private ?DateTimeImmutable $dataGeracao = null
    ) {
        $this->validarPsicologoId($this->psicologoId);

        $this->validarPeriodo(
            $this->periodoInicial,
            $this->periodoFinal
        );

        $this->validarValorLiquido($this->valorLiquido);

        $this->validarSituacaoPagamento();
    }

    public function marcarComoPago(
        DateTimeImmutable $dataPagamento
    ): void {
        if ($this->status === TipoStatusRepasse::PAGO) {
            throw new DomainException(
                'O repasse já está marcado como pago.'
            );
        }

        $this->validarDataPagamento($dataPagamento);

        $this->status = TipoStatusRepasse::PAGO;
        $this->dataPagamento = $dataPagamento;
    }

    private function validarPsicologoId(int $psicologoId): void
    {
        if ($psicologoId <= 0) {
            throw new InvalidArgumentException(
                'O psicólogo do repasse é obrigatório.'
            );
        }
    }

    private function validarPeriodo(
        DateTimeImmutable $periodoInicial,
        DateTimeImmutable $periodoFinal
    ): void {
        if ($periodoInicial > $periodoFinal) {
            throw new InvalidArgumentException(
                'O período inicial não pode ser posterior ao período final.'
            );
        }
    }

    private function validarValorLiquido(float $valorLiquido): void
    {
        if ($valorLiquido <= 0) {
            throw new InvalidArgumentException(
                'O valor líquido do repasse deve ser maior que zero.'
            );
        }
    }

    private function validarSituacaoPagamento(): void
    {
        if (
            $this->status === TipoStatusRepasse::PAGO
            && $this->dataPagamento === null
        ) {
            throw new InvalidArgumentException(
                'A data de pagamento é obrigatória para um repasse pago.'
            );
        }

        if (
            $this->status === TipoStatusRepasse::ABERTO
            && $this->dataPagamento !== null
        ) {
            throw new InvalidArgumentException(
                'Um repasse aberto não pode possuir data de pagamento.'
            );
        }

        if ($this->dataPagamento !== null) {
            $this->validarDataPagamento(
                $this->dataPagamento
            );
        }
    }

    private function validarDataPagamento(
        DateTimeImmutable $dataPagamento
    ): void {
        if (
            $dataPagamento->format('Y-m-d')
            < $this->periodoFinal->format('Y-m-d')
        ) {
            throw new InvalidArgumentException(
                'A data de pagamento não pode ser anterior ao fim do período.'
            );
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPsicologoId(): int
    {
        return $this->psicologoId;
    }

    public function getPeriodoInicial(): DateTimeImmutable
    {
        return $this->periodoInicial;
    }

    public function getPeriodoFinal(): DateTimeImmutable
    {
        return $this->periodoFinal;
    }

    public function getValorLiquido(): float
    {
        return $this->valorLiquido;
    }

    public function getStatus(): TipoStatusRepasse
    {
        return $this->status;
    }

    public function getDataPagamento(): ?DateTimeImmutable
    {
        return $this->dataPagamento;
    }

    public function getDataGeracao(): ?DateTimeImmutable
    {
        return $this->dataGeracao;
    }
}
