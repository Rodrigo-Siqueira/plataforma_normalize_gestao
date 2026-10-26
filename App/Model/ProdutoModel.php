<?php

declare(strict_types=1);

namespace App\Model;

use App\Enum\TipoProduto;
use DomainException;

class ProdutoModel
{
    public function __construct(
        private ?int $id,
        private string $nome,
        private TipoProduto $tipo,
        private int $numeroSessoes,
        private float $preco,
        private int $validadeDias,
        private int $ativo = 1
    ) {
        $this->validar();
    }

    private function validar(): void
    {
        $this->validarTipoProduto();
        $this->validarLimitesSessoes();
        $this->validarLimitesValidade();
        $this->validarLimitesPreco();
    }

    private function validarTipoProduto(): void
    {
        if ($this->tipo === TipoProduto::AVULSO && $this->numeroSessoes !== 1) {
            throw new DomainException('Um produto AVULSO deve conter exatamente 1 sessão.');
        }

        if ($this->tipo === TipoProduto::PACOTE && $this->numeroSessoes <= 1) {
            throw new DomainException('Um PACOTE deve conter mais de 1 sessão.');
        }
    }

    private function validarLimitesValidade(): void
    {
        if ($this->validadeDias < 30 || $this->validadeDias > 90) {
            throw new DomainException('A validade do produto deve estar entre 30 e 90 dias.');
        }
    }

    private function validarLimitesSessoes(): void
    {
        if ($this->numeroSessoes < 1 || $this->numeroSessoes > 52) {
            throw new DomainException('A quantidade de sessões deve estar entre 1 e 52.');
        }
    }

    private function validarLimitesPreco(): void
    {
        $valorPorSessao = $this->preco / $this->numeroSessoes;
        if ($valorPorSessao < 70.00) {
            throw new DomainException('O preço por sessão não pode ser inferior a R$ 70,00.');
        }

        if ($this->preco > 5000.00) {
            throw new DomainException('O preço máximo permitido para um produto é R$ 5.000,00.');
        }
    }

    public function alterarNome(string $nome): void
    {
        $nome = trim($nome);

        if ($nome === '') {
            throw new DomainException('O nome do produto é obrigatório.');
        }
        $this->nome = $nome;
    }

    public function transformarEmAvulso(): void
    {
        $this->tipo = TipoProduto::AVULSO;
        $this->numeroSessoes = 1;
        $this->validar();
    }

    public function transformarEmPacote(int $numeroSessoes): void
    {
        $this->tipo = TipoProduto::PACOTE;
        $this->numeroSessoes = $numeroSessoes;
        $this->validar();
    }

    public function alterarPreco(float $preco): void
    {
        $this->preco = $preco;
        $this->validar();
    }

    public function alterarValidadeDias(int $validadeDias): void
    {
        $this->validadeDias = $validadeDias;
        $this->validar();
    }

    public function ativar(): void
    {
        $this->ativo = 1;
    }

    public function desativar(): void
    {
        $this->ativo = 0;
    }

    public function ehPacote(): bool
    {
        return $this->tipo === TipoProduto::PACOTE;
    }

    public function ehAvulso(): bool
    {
        return $this->tipo === TipoProduto::AVULSO;
    }

    // ==========================================
    // MÉTODOS GETERS
    // ==========================================

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNome(): string
    {
        return $this->nome;
    }

    public function getTipo(): TipoProduto
    {
        return $this->tipo;
    }

    public function getNumeroSessoes(): int
    {
        return $this->numeroSessoes;
    }

    public function getPreco(): float
    {
        return $this->preco;
    }

    public function getValidadeDias(): int
    {
        return $this->validadeDias;
    }

    public function isAtivo(): int
    {
        return $this->ativo;
    }
}
