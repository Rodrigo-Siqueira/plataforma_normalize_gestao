<?php

namespace App\Dto\Output;

use Normalize\Helper\Helper;

readonly class ProdutoOutputDTO
{
    public function __construct(
        public int $id,
        public string $nome,
        public string $tipo, // 'PACOTE' ou 'AVULSO'
        public int $numeroSessoes,
        public float $preco,
        public string $precoFormatado,
        public int $validadeDias,
        public int $ativo
    ) {}

    public static function criarDeEntidade(object $dados): self
    {
        return new self(
            id: (int) $dados->id,
            nome: (string) $dados->nome,
            tipo: (string) $dados->tipo,
            numeroSessoes: (int) $dados->numero_sessoes,
            preco: (float) $dados->preco,
            precoFormatado: (string) Helper::formatarMoeda($dados->preco),
            validadeDias: (int) $dados->validade_dias,
            ativo: (int) $dados->ativo,
        );
    }

    public static function criarColecao(array $listaDeDados): array
    {
        return array_map(
            fn(object $dados) => self::criarDeEntidade($dados),
            $listaDeDados
        );
    }
}
