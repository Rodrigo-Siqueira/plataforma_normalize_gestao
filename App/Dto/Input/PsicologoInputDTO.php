<?php

declare(strict_types=1);

namespace App\Dto\Input;

readonly class PsicologoInputDTO
{
    public function __construct(
        public ?int $id,
        public ?int $pessoaId,
        public PessoaInputDTO $pessoa,
        public string $crp,
        public ?float $comissao,
        public string $dataInicio,
        public int $ativo
    ) {}

    public static function criarDeArray(array $dados): self
    {
        $comissaoBruta = trim($dados['comissao'] ?? '');

        return new self(
            id: (isset($dados['id']) && $dados['id'] !== '') ? (int) $dados['id'] : null,
            pessoaId: (isset($dados['pessoaId']) && $dados['pessoaId'] !== '') ? (int) $dados['pessoaId'] : null,
            pessoa: PessoaInputDTO::criarDeArray($dados),
            crp: trim($dados['crp'] ?? ''),
            comissao: $comissaoBruta !== '' ? (float) $comissaoBruta : null,
            dataInicio: !empty($dados['dataInicio']) ? trim($dados['dataInicio']) : date('Y-m-d'),
            ativo: isset($dados['ativo']) ? (int) trim($dados['ativo']) : 1
        );
    }
}