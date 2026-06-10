<?php

declare(strict_types=1);

namespace App\Service;

use App\Dto\Input\PacienteInputDTO;
use App\Model\PacienteModel;
use App\Repository\PacienteRepository;
use Exception;
use Normalize\Database\Transacao;

class PacienteService
{
    private PessoaService $pessoaServico;
    private PacienteRepository $repositorio;
    private ResponsavelPacienteService $responsavelServico;

    public function __construct()
    {
        $this->pessoaServico = new PessoaService();
        $this->repositorio = new PacienteRepository();
        $this->responsavelServico = new ResponsavelPacienteService();
    }

    public function salvar(PacienteInputDTO $dto)
    {
        Transacao::abrir();
        try {
            $pessoaPacienteId = $this->pessoaServico->buscarOuCriar($dto->paciente);

            $paciente = new PacienteModel(
                id: $dto->id,
                pessoaId: $pessoaPacienteId,
                psicologoResponsavelId: $dto->psicologoResponsavelId,
                ativo: $dto->ativo
            );

            if ($paciente->getId() === null) {
                $pacienteId = $this->repositorio->salvar($paciente);
            } else {
                $this->repositorio->alterar($paciente);
                $pacienteId = $paciente->getId();
            }

            if ($dto->responsavel) {
                $this->responsavelServico->salvarResponsavel($pacienteId, $pessoaPacienteId, $dto->responsavel);
            }

            Transacao::aplicarOperacao();
        } catch (Exception $e) {
            Transacao::registrarLog('Erro ao salvar paciente: ' . $e->getMessage());
            Transacao::desfazerOperacao();
            throw $e;
        }
    }

    public function excluir(int $id): void
    {
        $paciente = $this->repositorio->buscarPacientePorId($id);

        if (!$paciente) {
            throw new Exception('Paciente não encontrado.');
        }

        $paciente->desativar();
        $this->repositorio->atualizarStatus($paciente);
    }
}