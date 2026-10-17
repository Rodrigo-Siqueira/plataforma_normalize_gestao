<?php

declare(strict_types=1);

namespace App\Service;

use App\Dto\Input\SessaoInputDTO;
use App\Dto\Output\SessaoOutputDTO;
use App\Enum\TipoSessaoStatus;
use App\Model\SessaoModel;
use App\Repository\SessaoRepository;
use App\Repository\VendaRepository;
use DateInterval;
use DomainException;
use Normalize\Database\Transacao;
use Throwable;

class SessaoService
{
    private VendaRepository $vendaRepositorio;
    private SessaoRepository $sessaoRepositorio;

    public function __construct()
    {
        $this->vendaRepositorio = new VendaRepository();
        $this->sessaoRepositorio = new SessaoRepository();
    }

    public function criarSessao(
        SessaoInputDTO $dtoSessao,
        int $qtdSessoes = 1,
        int $intervaloDias = 7
    ): void {
        Transacao::abrir();

        try {
            $totalCreditos =
                $this->vendaRepositorio
                ->somarCreditosPagosPorPaciente(
                    $dtoSessao->pacienteId
                );

            $sessoesAgendadas =
                $this->sessaoRepositorio
                ->contarSessoesPorPaciente(
                    $dtoSessao->pacienteId
                );

            $saldo = $totalCreditos - $sessoesAgendadas;

            if ($saldo < $qtdSessoes) {
                throw new DomainException(
                    "Agendamento bloqueado: Paciente possui saldo de "
                        . "apenas {$saldo} sessão(ões), mas tentou agendar "
                        . "{$qtdSessoes}."
                );
            }

            $vendaDisponivelId =
                $this->vendaRepositorio
                ->buscarIdProximaVendaDisponivel(
                    $dtoSessao->pacienteId
                );

            if (!$vendaDisponivelId) {
                throw new DomainException(
                    "Inconsistência: Nenhuma venda válida com crédito "
                        . "encontrada para vincular a sessão."
                );
            }

            $dataCalculada = $dtoSessao->dataAgendada;
            $salto = new DateInterval("P{$intervaloDias}D");

            for ($i = 1; $i <= $qtdSessoes; $i++) {
                $sessaoModel = new SessaoModel(
                    id: null,
                    pacienteId: $dtoSessao->pacienteId,
                    psicologoId: $dtoSessao->psicologoId,
                    vendaOrigemId: $vendaDisponivelId,
                    dataAgendada: $dataCalculada,
                    horaAgendada: $dtoSessao->horaAgendada,
                    status: $dtoSessao->status
                );

                $this->sessaoRepositorio->salvar($sessaoModel);

                $dataCalculada = $dataCalculada->add($salto);
            }

            Transacao::aplicarOperacao();
        } catch (Throwable $erro) {
            Transacao::registrarLog(
                'Erro ao salvar a sessão: '
                    . $erro->getMessage()
            );

            Transacao::desfazerOperacao();

            throw $erro;
        }
    }

    public function marcarSessaoComoRealizada(int $id): void
    {
        $dadosDaSessao =
            $this->sessaoRepositorio->buscarSessaoPorId($id);

        if (!$dadosDaSessao) {
            throw new DomainException(
                "Sessão não encontrada."
            );
        }

        $sessaoAtual =
            SessaoOutputDTO::criarDeEntidade(
                $dadosDaSessao
            );

        $sessaoModel =
            $this->criarModelo($sessaoAtual);

        $sessaoModel->marcarComoRealizada();

        $this->sessaoRepositorio
            ->atualizarStatus($sessaoModel);
    }

    public function remarcarSessao(
        SessaoInputDTO $dtoSessao
    ): void {
        if ($dtoSessao->id === null) {
            throw new DomainException(
                "O código da sessão é obrigatório "
                    . "para realizar a remarcação."
            );
        }

        Transacao::abrir();

        try {
            $dadosDaSessao =
                $this->sessaoRepositorio
                ->buscarSessaoPorId($dtoSessao->id);

            if (!$dadosDaSessao) {
                throw new DomainException(
                    "Sessão não encontrada."
                );
            }

            $sessaoAtual =
                SessaoOutputDTO::criarDeEntidade(
                    $dadosDaSessao
                );

            $sessaoModel =
                $this->criarModelo($sessaoAtual);

            /*
             * As referências originais precisam ser guardadas antes
             * da remarcação. Elas determinam quais são as sessões
             * seguintes e qual será o deslocamento das datas.
             */
            $dataOriginalAncora =
                $sessaoModel->getDataAgendada();

            $horaOriginalAncora =
                $sessaoModel->getHoraAgendada();

            $sessaoModel->remarcar(
                $dtoSessao->dataAgendada,
                $dtoSessao->horaAgendada
            );

            $this->sessaoRepositorio->alterar(
                $sessaoModel
            );

            if ($dtoSessao->remarcarSeguintes) {
                $diferencaEmDias = (int) $dataOriginalAncora
                    ->diff($dtoSessao->dataAgendada)
                    ->format('%r%a');

                $deltaDias = new DateInterval(
                    'P' . abs($diferencaEmDias) . 'D'
                );

                if ($diferencaEmDias < 0) {
                    $deltaDias->invert = 1;
                }

                $sessoesFuturas =
                    $this->sessaoRepositorio
                    ->buscarSessoesSeguintesDaVenda(
                        $sessaoModel->getVendaOrigemId(),
                        $dataOriginalAncora,
                        $horaOriginalAncora
                    );

                foreach ($sessoesFuturas as $sessaoFutura) {
                    $sessaoFuturaDto =
                        SessaoOutputDTO::criarDeEntidade(
                            $sessaoFutura
                        );

                    $modeloFuturo =
                        $this->criarModelo(
                            $sessaoFuturaDto
                        );

                    $novaDataDeslocada =
                        $modeloFuturo
                        ->getDataAgendada()
                        ->add($deltaDias);

                    $modeloFuturo->remarcar(
                        $novaDataDeslocada,
                        $dtoSessao->horaAgendada
                    );

                    $this->sessaoRepositorio->alterar(
                        $modeloFuturo
                    );
                }
            }

            Transacao::aplicarOperacao();
        } catch (Throwable $erro) {
            Transacao::registrarLog(
                'Erro ao remarcar sessão: '
                    . $erro->getMessage()
            );

            Transacao::desfazerOperacao();

            throw $erro;
        }
    }

    public function cancelarSessao(
        int $id,
        TipoSessaoStatus $tipoCancelamento,
        bool $cancelarSeguintes = false
    ): void {
        $this->validarTipoCancelamento(
            $tipoCancelamento
        );

        Transacao::abrir();

        try {
            $dadosDaSessao =
                $this->sessaoRepositorio
                ->buscarSessaoPorId($id);

            if (!$dadosDaSessao) {
                throw new DomainException(
                    "Sessão não encontrada."
                );
            }

            $sessaoAtual =
                SessaoOutputDTO::criarDeEntidade(
                    $dadosDaSessao
                );

            $sessaoModel =
                $this->criarModelo($sessaoAtual);

            /*
             * Guardamos data e hora antes da alteração do status.
             * Elas são usadas para localizar somente as sessões
             * posteriores dentro da mesma venda.
             */
            $dataOriginal =
                $sessaoModel->getDataAgendada();

            $horaOriginal =
                $sessaoModel->getHoraAgendada();

            $this->aplicarCancelamento(
                $sessaoModel,
                $tipoCancelamento
            );

            $this->sessaoRepositorio
                ->atualizarStatus($sessaoModel);

            if ($cancelarSeguintes) {
                $sessoesFuturas =
                    $this->sessaoRepositorio
                    ->buscarSessoesSeguintesDaVenda(
                        $sessaoModel->getVendaOrigemId(),
                        $dataOriginal,
                        $horaOriginal
                    );

                foreach ($sessoesFuturas as $sessaoFutura) {
                    $sessaoFuturaDto =
                        SessaoOutputDTO::criarDeEntidade(
                            $sessaoFutura
                        );

                    $modeloFuturo =
                        $this->criarModelo(
                            $sessaoFuturaDto
                        );

                    $this->aplicarCancelamento(
                        $modeloFuturo,
                        $tipoCancelamento
                    );

                    $this->sessaoRepositorio
                        ->atualizarStatus(
                            $modeloFuturo
                        );
                }
            }

            Transacao::aplicarOperacao();
        } catch (Throwable $erro) {
            Transacao::registrarLog(
                'Erro ao cancelar sessão: '
                    . $erro->getMessage()
            );

            Transacao::desfazerOperacao();

            throw $erro;
        }
    }

    private function aplicarCancelamento(
        SessaoModel $sessaoModel,
        TipoSessaoStatus $tipoCancelamento
    ): void {
        match ($tipoCancelamento) {
            TipoSessaoStatus::CANCELADA_PACIENTE =>
            $sessaoModel->cancelarPeloPaciente(),

            TipoSessaoStatus::CANCELADA_PSICOLOGO =>
            $sessaoModel->cancelarPeloPsicologo(),

            default => throw new DomainException(
                "O tipo de cancelamento informado é inválido."
            )
        };
    }

    private function validarTipoCancelamento(
        TipoSessaoStatus $tipoCancelamento
    ): void {
        if (
            $tipoCancelamento
            !== TipoSessaoStatus::CANCELADA_PACIENTE
            && $tipoCancelamento
            !== TipoSessaoStatus::CANCELADA_PSICOLOGO
        ) {
            throw new DomainException(
                "O tipo de cancelamento informado é inválido."
            );
        }
    }

    private function criarModelo(
        SessaoOutputDTO $sessao
    ): SessaoModel {
        if (
            $sessao->pacienteId === null
            || $sessao->psicologoId === null
            || $sessao->vendaOrigemId === null
        ) {
            throw new DomainException(
                "A sessão possui dados obrigatórios incompletos."
            );
        }

        return new SessaoModel(
            id: $sessao->id,
            pacienteId: $sessao->pacienteId,
            psicologoId: $sessao->psicologoId,
            vendaOrigemId: $sessao->vendaOrigemId,
            dataAgendada: $sessao->dataAgendada,
            horaAgendada: $sessao->horaAgendada,
            status: $sessao->status,
            loteRepasseId: $sessao->loteRepasseId
        );
    }
}
