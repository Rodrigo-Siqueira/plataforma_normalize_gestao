<?php

declare(strict_types=1);

namespace App\Dto\Input;

readonly class PessoaInputDTO
{
    public function __construct(
        public ?int $id,
        public string $nome,
        public string $contato
    ){}

    public static function criarDeArray(array $dados): self
    {
        return new self(
            id: (isset($dados['pessoaId']) && $dados['pessoaId'] !== '') ? (int) $dados['pessoaId'] : null,
            nome: trim($dados['nome'] ?? ''),
            contato: trim($dados['contato'] ?? '')
        );
    }
}
