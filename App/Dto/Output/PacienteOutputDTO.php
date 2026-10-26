<?php

declare(strict_types=1);

namespace App\Dto\Output;

use Normalize\Helper\Helper;

readonly class PacienteOutputDTO
{
    public function __construct(
        public int $id,
        public int $pessoaPacienteId,
        public string $nome,
        public string $contato,
        public bool $ativo,
        public int $psicologoId,
        public string $psicologo,
        public ?ResponsavelPacienteOutputDTO $responsavel
    ) {}

    public static function criarDeEntidade(object $dadosPaciente, ?ResponsavelPacienteOutputDTO $responsavelDTO = null): self
    {
        return new self(
            id: (int) $dadosPaciente->id,
            pessoaPacienteId: (int) ($dadosPaciente->pessoaPacienteId ?? 0),
            nome: (string) $dadosPaciente->pacienteNome,
            contato: (string) Helper::formatarTelefone($dadosPaciente->pacienteContato),
            ativo: (bool) $dadosPaciente->ativo,
            psicologoId: (int) $dadosPaciente->psicologoId,
            psicologo: (string) $dadosPaciente->psicologo,
            responsavel: $responsavelDTO
        );
    }

    public static function criarColecao(array $listaDeDados): array
    {
        return array_map(function (object $dados) {
            // 1. Instancia o DTO do Responsável se vier na query de listagem e não for PROPRIO
            $responsavelDTO = null;
            if (!empty($dados->vinculo) && $dados->vinculo !== 'PROPRIO') {
                $responsavelDTO = ResponsavelPacienteOutputDTO::criarDeEntidade($dados);
            }

            // 2. Passa o objeto bruto do paciente e o DTO do responsável para a fábrica principal
            return self::criarDeEntidade($dados, $responsavelDTO);
        }, $listaDeDados);
    }
}
