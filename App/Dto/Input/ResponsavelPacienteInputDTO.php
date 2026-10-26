<?php

declare(strict_types=1);

namespace App\Dto\Input;

use App\Enum\TipoParentesco;

readonly class ResponsavelPacienteInputDTO
{
    public function __construct(
        public ?int $id,
        public ?PessoaInputDTO $pessoaResponsavel,
        public TipoParentesco $parentesco
    ) {}

    public static function criarDeArray(array $dados): self
    {
        $pessoaResponsavel = null;

        if (($dados['responsavelVinculo'] ?? '') !== 'PROPRIO') {
            $pessoaResponsavel = new PessoaInputDTO(
                id: (int) ($dados['responsavelPessoaId'] ?? 0) ?: null,
                nome: (string) ($dados['responsavelNome'] ?? ''),
                contato: (string) ($dados['responsavelContato'] ?? '')
            );
        }

        return new self(
            id: (int) ($dados['responsavelId'] ?? 0) ?: null,
            pessoaResponsavel:  $pessoaResponsavel,
            parentesco: TipoParentesco::tryFrom($dados['responsavelVinculo'] ?? '') ?? TipoParentesco::OUTRO
        );
    }
}
