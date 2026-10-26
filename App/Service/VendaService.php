<?php

declare(strict_types=1);

namespace App\Service;

use App\Dto\Input\VendaInputDTO;
use App\Model\VendaModel;
use App\Repository\PacienteRepository;
use App\Repository\VendaRepository;
use DomainException;
use Exception;
use Normalize\Database\Transacao;

class VendaService
{
    private VendaRepository $vendaRepositorio;
    private PacienteRepository $pacienteRepositorio;

    public function __construct()
    {
        $this->vendaRepositorio = new VendaRepository();
        $this->pacienteRepositorio = new PacienteRepository();
    }

    public function processarVenda(VendaInputDTO $dto): void
    {
        $pacienteEstaAtivo = $this->pacienteRepositorio->pacienteEstaAtivo($dto->pacienteId);

        if (!$pacienteEstaAtivo) {
            throw new DomainException('Paciente está inativo.');
        }

        Transacao::abrir();
        try {
            $vendaModel = new VendaModel(
                id: $dto->id,
                pacienteId: $dto->pacienteId,
                responsavelId: $dto->responsavelId,
                produtoId: $dto->produtoId,
                valorCobrado: $dto->valorCobrado,
                dataVenda: $dto->dataVenda,
                dataPagamento: $dto->dataPagamento,
                formaPagamento: $dto->formaPagamento,
                status: $dto->status
            );

            if ($vendaModel->getId() === null) {
                $this->vendaRepositorio->salvar($vendaModel);
            } else {
                $this->vendaRepositorio->alterar($vendaModel);
            }

            Transacao::aplicarOperacao();
        } catch (Exception $e) {
            Transacao::registrarLog('Erro ao salvar a venda: ' . $e->getMessage());
            Transacao::desfazerOperacao();
            throw $e;
        }
    }
}