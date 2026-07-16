<?php

declare(strict_types=1);

namespace App\Model;

use App\Enum\TipoCategoriaDespesa;
use App\Enum\TipoDespesa;
use App\Enum\TipoStatusDespesa;
use DateTimeImmutable;
use DomainException;

class DespesaModel
{
    private const VALOR_LIMITE = 15000;

    public function __construct(
        private ?int $id,
        private string $descricao,
        private TipoDespesa $tipo,
        private TipoCategoriaDespesa $categoria,
        private float $valor,
        private DateTimeImmutable $dataDespesa,
        private ?DateTimeImmutable $dataPagamento,
        private TipoStatusDespesa $status
    ) {
        $this->validar(
            $this->descricao,
            $this->valor,
            $this->dataDespesa,
            $this->dataPagamento,
            $this->status
        );
    }

    private function validar(
        string $descricao,
        float $valor,
        DateTimeImmutable $dataDespesa,
        ?DateTimeImmutable $dataPagamento,
        TipoStatusDespesa $status
    ): void {
        $this->validarDescricao($descricao);
        $this->validarValor($valor);
        $this->validarRegrasDeDatasEStatus($dataDespesa, $dataPagamento, $status);
    }

    private function validarValor(float $valor): void
    {
        if ($valor <= 0) {
            throw new DomainException('O valor deve ser maior que Zero.');
        }
        
        if ($valor > self::VALOR_LIMITE) {
            throw new DomainException("Não é permitido gerar uma despesa com o valor de R$ {$valor}.");
        }
    }

    private function validarDescricao(string $descricao): void
    {
        if (trim($descricao) === '') {
            throw new DomainException('A descrição da despesa é obrigatória e não pode estar em branco.');
        }
    }

    private function validarRegrasDeDatasEStatus(
        DateTimeImmutable $dataDespesa,
        ?DateTimeImmutable $dataPagamento,
        TipoStatusDespesa $status
    ): void 
    {
        if ($dataPagamento !== null && $dataPagamento < $dataDespesa) {
            throw new DomainException('A data de pagamento não pode ser anterior à data de emissão da despesa.');
        }

        if ($status === TipoStatusDespesa::PAGO && $dataPagamento === null) {
            throw new DomainException('Uma despesa com status PAGO deve obrigatoriamente possuir uma data de pagamento.');
        }

        if ($status !== TipoStatusDespesa::PAGO && $dataPagamento !== null) {
            throw new DomainException('Uma despesa pendente ou cancelada não deve possuir uma data de pagamento vinculada.');
        }
    }

    public function registrarPagamento(DateTimeImmutable $dataPagamento): void
    {
        $this->validarRegrasDeDatasEStatus($this->dataDespesa, $dataPagamento, TipoStatusDespesa::PAGO);
        $this->dataPagamento = $dataPagamento;
        $this->status = TipoStatusDespesa::PAGO;
    }

    public function reverterParaPendente(): void
    {
        $this->validarRegrasDeDatasEStatus($this->dataDespesa, null, TipoStatusDespesa::PENDENTE);
        $this->dataPagamento = null;
        $this->status = TipoStatusDespesa::PENDENTE;
    }

    public function alterarDescricao(string $descricao): void
    {
        $this->validarDescricao($descricao);
        $this->descricao = trim($descricao);
    }

    public function alterarTipo(TipoDespesa $tipo): void
    {
        $this->tipo = $tipo;
    }

    public function alterarCategoria(TipoCategoriaDespesa $categoria): void
    {
        $this->categoria = $categoria;
    }

    public function alterarValor(float $valor): void
    {
        $this->validarValor($valor);
        $this->valor = $valor;
    }

    public function alterarDataDespesa(DateTimeImmutable $dataDespesa): void
    {
        $this->validarRegrasDeDatasEStatus($dataDespesa, $this->dataPagamento, $this->status);
        $this->dataDespesa = $dataDespesa;
    }

    // ==========================================
    // MÉTODOS GETTERS
    // ==========================================

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDescricao(): string
    {
        return $this->descricao;
    }

    public function getTipo(): TipoDespesa
    {
        return $this->tipo;
    }

    public function getCategoria(): TipoCategoriaDespesa
    {
        return $this->categoria;
    }

    public function getValor(): float
    {
        return $this->valor;
    }

    public function getDataDespesa(): DateTimeImmutable
    {
        return $this->dataDespesa;
    }

    public function getDataPagamento(): ?DateTimeImmutable
    {
        return $this->dataPagamento;
    }

    public function getStatus(): TipoStatusDespesa
    {
        return $this->status;
    }
}
