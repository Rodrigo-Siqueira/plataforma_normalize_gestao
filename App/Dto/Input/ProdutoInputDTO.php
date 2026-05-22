<?php

namespace App\Dto\Input;

readonly class ProdutoInputDTO
{
    public function __construct(
        public ?int $id,
        public string $nome,
        public string $tipo, // 'PACOTE' ou 'AVULSO'
        public int $numeroSessoes,
        public float $preco,
        public int $validadeDias,
        public int $ativo
    ) {}

    public static function criarDeArray(array $dados): self
    {
        return new self(
            id: isset($dados['id']) && $dados['id'] !== '' ? (int) $dados['id'] : null,
            nome: trim($dados['nome'] ?? ''),
            tipo: trim($dados['tipo'] ?? 'AVULSO'),
            numeroSessoes: (int) ($dados['numeroSessoes'] ?? 1),
            preco: (float) ($dados['preco'] ?? 0.0),
            validadeDias: (int) ($dados['validadeDias'] ?? 30),
            ativo: isset($dados['ativo']) ? (int) $dados['ativo'] : 1
        );
    }
}
