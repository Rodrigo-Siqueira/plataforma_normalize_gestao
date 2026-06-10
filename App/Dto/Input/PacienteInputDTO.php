<?php

declare(strict_types=1);

namespace App\Dto\Input;

readonly class PacienteInputDTO
{
    public function __construct(
        public ?int $id,
        public int $psicologoResponsavelId,
        public int $ativo,
        public PessoaInputDTO $paciente,
        public ?ResponsavelPacienteInputDTO $responsavel
    ) {}

    public static function criarDeArray(array $dados): self
    {
        return new self(
            id: (int) ($dados['id'] ?? 0) ?: null,
            psicologoResponsavelId: (int) ($dados['psicologoId'] ?? 0),
            ativo: (int) ($dados['ativo'] ?? 1),
            paciente: new PessoaInputDTO(
                id: (int) ($dados['pessoaPacienteId'] ?? 0) ?: null,
                nome: (string) ($dados['pacienteNome'] ?? ''),
                contato: (string) ($dados['pacienteContato'] ?? '')
            ),
            responsavel: ResponsavelPacienteInputDTO::criarDeArray($dados)
        );
    }
}