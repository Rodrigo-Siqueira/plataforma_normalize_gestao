<?php

declare(strict_types=1);

namespace App\Service;

use App\Dto\Input\ResponsavelPacienteInputDTO;
use App\Enum\TipoParentesco;
use App\Model\ResponsavelPacienteModel;
use App\Repository\ResponsavelPacienteRepository;

class ResponsavelPacienteService
{
    private PessoaService $pessoaServico;
    private ResponsavelPacienteRepository $repositorio;

    public function __construct()
    {
        $this->pessoaServico = new PessoaService();
        $this->repositorio = new ResponsavelPacienteRepository();
    }

    public function salvarResponsavel(int $pacienteId, int $pessoaPacienteId, ResponsavelPacienteInputDTO $dto)
    {
        $pessoaResponsavelId = null;

        if ($dto->parentesco === TipoParentesco::PROPRIO) {
            $pessoaResponsavelId = $pessoaPacienteId;
        } else {
            $pessoaResponsavelId = $this->pessoaServico->buscarOuCriar($dto->pessoaResponsavel);
        }

        $idVinculo = $dto->id;

        // Verificação necessária porque o front-end esconde o ID se for PROPRIO.
        // Buscamos no banco para evitar a duplicidade (Unique Constraint).
        if ($idVinculo === null) {
            $vinculoExistente = $this->repositorio->buscarPorPacienteId($pacienteId);
            if ($vinculoExistente) {
                $idVinculo = (int) $vinculoExistente->responsavelId;
            }
        }

        $responsavel = new ResponsavelPacienteModel(
            id: $idVinculo,
            pessoaId: $pessoaResponsavelId,
            pacienteId: $pacienteId,
            parentesco: $dto->parentesco
        );

        if ($responsavel->getId() === null) {
            $this->repositorio->salvar($responsavel);
        } else {
            $this->repositorio->alterar($responsavel);
        }
    }
}
