<?php

declare(strict_types=1);

namespace App\Service;

use App\Dto\Input\PsicologoInputDTO;
use App\Model\PsicologoModel;
use App\Repository\PsicologoRepository;
use Exception;
use Normalize\Database\Transacao;

class PsicologoService
{
    private PessoaService $pessoaServico;
    private PsicologoRepository $repositorio;

    public function __construct()
    {
        $this->pessoaServico = new PessoaService();
        $this->repositorio = new PsicologoRepository();
    }

    public function salvar(PsicologoInputDTO $dto): void
    {
        Transacao::abrir();
        try {
            $pessoaId = $this->pessoaServico->buscarOuCriar($dto->pessoa);

            $psicologo = new PsicologoModel(
                id: $dto->id,
                pessoaId: $pessoaId,
                crp: $dto->crp,
                comissao: $dto->comissao,
                dataInicio: $dto->dataInicio,
                ativo: $dto->ativo
            );

            if ($psicologo->getId() === null) {
                $this->repositorio->salvar($psicologo);
            } else {
                $this->repositorio->alterar($psicologo);
            }

            Transacao::aplicarOperacao();
        } catch (Exception $e) {
            Transacao::registrarLog('Erro ao salvar psicólogo: ' . $e->getMessage());
            Transacao::desfazerOperacao();
            throw $e;
        }
    }

    public function excluir(int $id): void
    {
        $psicologo = $this->repositorio->buscarPsicologoPorId($id);

        if (!$psicologo) {
            throw new Exception('Psicologo não encontrado.');
        }

        $psicologo->desativar();
        $this->repositorio->atualizarStatus($psicologo);
    }
}
