<?php

declare(strict_types=1);

namespace App\Model;

use DomainException;

class PessoaModel
{
    public function __construct(
        private ?int $id,
        private string $nome,
        private string $contato
    ) {
        $this->validar();
    }

    private function validar(): void
    {
        $this->validarNome();
        $this->validarContato();
    }

    private function validarNome(): void
    {
        if (mb_strlen($this->nome) < 3 || mb_strlen($this->nome) > 100) {
            throw new DomainException('O nome deve ser maior que 3 caracteres e menor que 100 caracteres.');
        }

        if (!preg_match('/^[\p{L}\s]+$/u', $this->nome)) {
            throw new DomainException('O nome deve conter apenas letras e espaços.');
        }
    }

    private function validarContato(): void
    {
        if (mb_strlen($this->contato) < 10) {
            throw new DomainException('O contato deve ser maior que 9 caracteres.');
        }

        if (!preg_match('/^\d{1,11}$/', $this->contato)) {
            throw new DomainException('O contato deve ter o máximo de 11 digitos.');
        }
    }

    public function alterarNome(string $nome): void
    {
        $this->nome = trim($nome);
        $this->validarNome();
    }

    public function alterarContato(string $contato): void
    {
        $this->contato = trim($contato);
        $this->validarContato();
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

    public function getContato(): string
    {
        return $this->contato;
    }
}
