<?php

declare(strict_types=1);

namespace App\Service;

use App\Config\Caminhos;
use App\Dto\Input\RepasseInputDTO;
use App\Dto\Output\RepasseOutputDTO;
use App\Enum\TipoStatusRepasse;
use App\Model\RepasseModel;
use App\Repository\RepasseRepository;
use DateTimeImmutable;
use DomainException;
use Normalize\Database\Transacao;
use Normalize\Log\Logger;
use Normalize\Log\LoggerTXT;
use Throwable;

class RepasseService
{
    private RepasseRepository $repositorio;
    private Logger $loggerAplicacao;
    private Logger $loggerErros;
    private Logger $loggerDesempenho;

    public function __construct()
    {
        $this->repositorio = new RepasseRepository();
        $this->loggerAplicacao = new LoggerTXT(Caminhos::logAplicacao());
        $this->loggerErros = new LoggerTXT(Caminhos::logErros());
        $this->loggerDesempenho = new LoggerTXT(Caminhos::logDesempenho());
    }

    public function criarLoteRepasse(RepasseInputDTO $repasseDto): int
    {
        $inicioExecucao = hrtime(true);

        Transacao::abrir();
        try {
            $sessoesElegiveis = $this->listarSessoesElegiveisParaRepasse($repasseDto);

            if ($sessoesElegiveis === []) {
                throw new DomainException(
                    'Nenhuma sessão realizada está disponível '
                        . 'para repasse no período informado.'
                );
            }
            $valorLiquido = $this->calcularValorTotalDoRepasse($sessoesElegiveis);
            $idsDasSessoes = $this->listarIdsDasSessoesProcessadas($sessoesElegiveis);

            $repasseModel = new RepasseModel(
                id: null,
                psicologoId: $repasseDto->psicologoId,
                periodoInicial: $repasseDto->periodoInicial,
                periodoFinal: $repasseDto->periodoFinal,
                valorLiquido: $valorLiquido,
                status: TipoStatusRepasse::ABERTO,
                dataPagamento: null
            );
            $loteGeradoId = $this->repositorio->salvar($repasseModel);

            $this->repositorio->vincularSessoesAoLote($loteGeradoId, $idsDasSessoes);

            Transacao::aplicarOperacao();

            $duracaoMs = $this->calcularDuracaoEmMs($inicioExecucao);

            $contexto = [
                'processo' => 'gerar_lote_repasse',
                'loteId' => $loteGeradoId,
                'psicologoId' => $repasseDto->psicologoId,
                'periodoInicial' =>
                $repasseDto->periodoInicial->format('Y-m-d'),
                'periodoFinal' =>
                $repasseDto->periodoFinal->format('Y-m-d'),
                'quantidadeSessoes' => count($idsDasSessoes),
                'valorLiquido' => $valorLiquido,
                'duracaoMs' => $duracaoMs,
            ];

            $this->loggerAplicacao->escrever(
                mensagem: 'Lote de repasse gerado com sucesso.',
                nivel: 'INFO',
                contexto: $contexto
            );

            $this->loggerDesempenho->escrever(
                mensagem: 'Geração do lote de repasse.',
                nivel: 'DESEMPENHO',
                contexto: $contexto
            );

            return $loteGeradoId;
        } catch (Throwable $erro) {
            Transacao::desfazerOperacao();

            $this->loggerErros->escrever(
                mensagem: 'Falha ao gerar lote de repasse.',
                nivel: 'ERRO',
                contexto: [
                    'processo' => 'gerar_lote_repasse',
                    'psicologoId' => $repasseDto->psicologoId,
                    'periodoInicial' =>
                    $repasseDto->periodoInicial->format('Y-m-d'),
                    'periodoFinal' =>
                    $repasseDto->periodoFinal->format('Y-m-d'),
                    'duracaoMs' =>
                    $this->calcularDuracaoEmMs(
                        $inicioExecucao
                    ),
                ],
                excecao: $erro
            );

            throw $erro;
        }
    }

    public function marcarRepasseComoPago(
        int $id
    ): void {
        $dataPagamento = new DateTimeImmutable();
        $dadosDoRepasse = $this->repositorio->buscarRepassePorId($id);

        if (!$dadosDoRepasse) {
            throw new DomainException("Repasse não encontrado.");
        }

        $repasseAtual = RepasseOutputDTO::criarDeEntidade($dadosDoRepasse);

        $repasseModel = new RepasseModel(
            id: $repasseAtual->id,
            psicologoId: $repasseAtual->psicologoId,
            periodoInicial: $repasseAtual->periodoInicial,
            periodoFinal: $repasseAtual->periodoFinal,
            valorLiquido: $repasseAtual->valorLiquido,
            status: $repasseAtual->status,
            dataPagamento: $repasseAtual->dataPagamento,
            dataGeracao: $repasseAtual->dataGeracao
        );

        $repasseModel->marcarComoPago($dataPagamento);
        $this->repositorio->atualizarStatus($repasseModel);
    }

    public function alterarRepasse(RepasseInputDTO $repasseDto): void
    {
        if ($repasseDto->id === null) {
            throw new DomainException(
                'O código do repasse é obrigatório para alteração.'
            );
        }

        if ($repasseDto->valorLiquido === null) {
            throw new DomainException(
                'O valor líquido do repasse é obrigatório para alteração.'
            );
        }

        $dadosDoRepasse = $this->repositorio->buscarRepassePorId($repasseDto->id);

        if (!$dadosDoRepasse) {
            throw new DomainException('Repasse não encontrado.');
        }

        $repasseAtual = RepasseOutputDTO::criarDeEntidade($dadosDoRepasse);

        $repasseModel = new RepasseModel(
            id: $repasseAtual->id,
            psicologoId: $repasseAtual->psicologoId,
            periodoInicial: $repasseAtual->periodoInicial,
            periodoFinal: $repasseAtual->periodoFinal,
            valorLiquido: $repasseDto->valorLiquido,
            status: $repasseAtual->status,
            dataPagamento: $repasseAtual->dataPagamento,
            dataGeracao: $repasseAtual->dataGeracao
        );

        $this->repositorio->atualizarValorLiquido($repasseModel);
    }

    private function listarSessoesElegiveisParaRepasse(RepasseInputDTO $repasseDto): array
    {
        $listaSessoesDisponiveis = $this
            ->repositorio->buscarSessoesDoPsicologoParaGerarRepasse(
                $repasseDto->psicologoId,
                $repasseDto->periodoInicial,
                $repasseDto->periodoFinal
            );

        return $listaSessoesDisponiveis;
    }

    private function listarIdsDasSessoesProcessadas(array $sessoesElegiveis): array
    {
        $idsDasSessoes = [];

        foreach ($sessoesElegiveis as $sessao) {
            $idsDasSessoes[] = (int) $sessao->sessao_id;
        }

        return $idsDasSessoes;
    }

    private function calcularValorRepasseDaSessao(object $sessao): float
    {
        $valorCobrado = (float) $sessao->valor_cobrado;
        $numeroSessoes = (int) $sessao->numero_sessoes;
        $percentualComissao = (float) $sessao->percentual_comissao;

        if ($numeroSessoes <= 0) {
            throw new DomainException(
                'O número de sessões do produto deve ser maior que zero.'
            );
        }
        $valorPorSessao = $valorCobrado / $numeroSessoes;

        return round($valorPorSessao * $percentualComissao, 2);
    }

    private function calcularValorTotalDoRepasse(array $sessoesElegiveis): float
    {
        $valorTotalRepasse = 0.0;

        foreach ($sessoesElegiveis as $sessao) {
            $valorTotalRepasse += $this->calcularValorRepasseDaSessao($sessao);
        }

        return round($valorTotalRepasse, 2);
    }

    private function calcularDuracaoEmMs(
        int $inicioExecucao
    ): float {
        return round(
            (hrtime(true) - $inicioExecucao) / 1_000_000,
            2
        );
    }
}
