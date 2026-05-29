<?php

declare(strict_types=1);

namespace App\Dto\Output;

use Normalize\Helper\Helper;

readonly class PsicologoOutputDTO
{
    public function __construct(
        public int $id,
        public int $pessoaId,
        public string $nome,
        public string $crp,
        public string $crpFormatado,
        public string $contato,
        public float $comissao,
        public string $dataInicio,
        public string $dataInicioFormatada,
        public int $ativo
    ) {}

    public static function criarDeEntidade(object $dados): self
    {
        return new self(
            id: (int) $dados->id,
            pessoaId: (int) $dados->pessoaId,
            nome: (string) $dados->nome,
            crp: (string) $dados->crp,
            crpFormatado: (string) Helper::mascaraCrp($dados->crp),
            contato: (string) Helper::formatarTelefone($dados->contato),
            comissao: (float) $dados->comissao,
            dataInicio: (string) $dados->dataInicio,
            dataInicioFormatada: (string) Helper::formatarData($dados->dataInicio),
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
    