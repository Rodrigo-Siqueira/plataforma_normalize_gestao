<?php

declare(strict_types=1);

namespace App\Dto\Output;

use App\Enum\TipoParentesco;
use Normalize\Helper\Helper;

readonly class ResponsavelPacienteOutputDTO
{
    public function __construct(
        public ?int $id,
        public ?int $responsavelPessoaId,
        public ?string $nome,
        public ?string $contato,
        public TipoParentesco $parentesco
    ) {}

    public static function criarDeEntidade(object $dados): self
    {
        return new self(
            id: (int) $dados->responsavelId,
            responsavelPessoaId: (int) $dados->responsavelPessoaId,
            nome: (string) $dados->responsavelNome,
            contato: (string) Helper::formatarTelefone($dados->responsavelContato),
            parentesco: TipoParentesco::from($dados->vinculo)
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
