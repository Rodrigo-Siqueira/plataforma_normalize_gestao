<?php

declare(strict_types=1);

namespace App\Controller;

use App\Dto\Input\SessaoInputDTO;
use App\Dto\Output\SessaoOutputDTO;
use App\Enum\TipoSessaoStatus;
use App\Repository\PacienteRepository;
use App\Repository\SessaoRepository;
use App\Service\SessaoService;
use App\Validation\SessaoValidator;
use DomainException;
use Exception;
use Normalize\Component\Mensagem;
use Normalize\Controller\BaseController;
use Normalize\Helper\Helper;
use Normalize\Session\Sessao;
use Override;
use Throwable;

class SessaoController extends BaseController
{
    private Sessao $sessao;
    private SessaoRepository $repositorio;
    private PacienteRepository $pacienteRepositorio;
    private SessaoService $servico;

    #[Override]
    public function __construct()
    {
        parent::__construct(CAMINHO_TEMPLATE);
        $this->sessao = new Sessao();
        $this->repositorio = new SessaoRepository();
        $this->pacienteRepositorio = new PacienteRepository();
        $this->servico = new SessaoService();
    }

    public function listar(): string
    {
        $totalItens = $this->repositorio->contarTotal();

        $paginacao = $this->montarPaginacao($totalItens);
        $dadosBrutos = $this->repositorio->listarPaginado($paginacao['itensPorPagina'], $paginacao['deslocamento']);
        $sessaoPaginado = SessaoOutputDTO::criarColecao($dadosBrutos);

        $mensagemHtml = '';
        $flashSucesso = $this->sessao->obterFlash('sucesso');
        $flashErro = $this->sessao->obterFlash('erro');
        if ($flashSucesso) {
            $mensagemHtml = (string) (new Mensagem())->sucesso($flashSucesso);
        } elseif ($flashErro) {
            $mensagemHtml = (string) (new Mensagem())->erro($flashErro);
        }

        return $this->template->renderizar('Views/Sessao/listar.html.twig', [
            'sessoes' => $sessaoPaginado,
            'totalSessoes' => $totalItens,
            'mensagemFlash' => $mensagemHtml,
            'paginacao' => [
                'atual' => $paginacao['paginaAtual'],
                'total' => $paginacao['totalPaginas']
            ]
        ]);
    }

    private function montarPaginacao(int $totalItens): array
    {
        $itensPorPagina =10;
        $paginaAtual = filter_input(INPUT_GET, 'pagina', FILTER_VALIDATE_INT) ?: 1;
        if ($paginaAtual < 1) {
            $paginaAtual = 1;
        }

        $deslocamento = ($paginaAtual - 1) * $itensPorPagina;
        $totalPaginas = (int) ceil($totalItens / $itensPorPagina);

        if ($paginaAtual > $totalPaginas && $totalPaginas > 0) {
            $paginaAtual = $totalPaginas;
            $deslocamento = ($paginaAtual - 1) * $itensPorPagina;
        }

        return [
            'itensPorPagina' => $itensPorPagina,
            'deslocamento' => $deslocamento,
            'paginaAtual' => $paginaAtual,
            'totalPaginas' => $totalPaginas
        ];
    }

    public function exibirFormularioCadastro(): string
    {
        return $this->template->renderizar('Views/Sessao/formulario.html.twig', [
            'pacientes' => $this->pacienteRepositorio->listarPacienteEpsicologo()
        ]);
    }

    public function exibirFormularioEdicao(int $id): string
    {
        $sessaoObjeto = $this->repositorio->buscarSessaoPorId($id);

        if (!$sessaoObjeto) {
            $this->sessao->criarFlash(
                'erro',
                'Sessão não encontrada.'
            );

            Helper::redirecionar('sessoes/listar');
        }

        $sessaoOutput = SessaoOutputDTO::criarDeEntidade($sessaoObjeto);

        return $this->template->renderizar('Views/Sessao/formulario.html.twig', [
            'sessao' => $sessaoOutput,
            'pacientes' => $this->pacienteRepositorio->listarPacienteEpsicologo()
        ]);
    }

    public function processarCadastro(): string
    {
        $dadosPost = filter_input_array(INPUT_POST) ?? [];
        return $this->processarFormulario($dadosPost);
    }

    public function processarEdicao(int $id): string
    {
        $dadosPost = filter_input_array(INPUT_POST) ?? [];
        $dadosPost['id'] = $id;
        return $this->processarFormulario($dadosPost);
    }

    public function processarSessaoRealizada(int $id): void
    {
        try {
            $this->servico->marcarSessaoComoRealizada($id);

            $this->sessao->criarFlash(
                'sucesso',
                'Sessão marcada como realizada!'
            );
        } catch (Throwable $erro) {
            $this->sessao->criarFlash(
                'erro',
                $erro->getMessage()
            );
        }

        Helper::redirecionar('sessoes/listar');
    }

    private function processarFormulario(array $dadosPost): string
    {
        try {
            $entradaDto = SessaoInputDTO::criarDeArray(
                $dadosPost
            );
        } catch (Throwable $erro) {
            return $this->renderizarFormularioComErros(
                $dadosPost,
                [
                    'global' =>
                    'Os dados informados para a sessão são inválidos.'
                ]
            );
        }

        $validador = new SessaoValidator();

        $erros = $validador->validar($entradaDto);

        if ($entradaDto->id === null) {
            $qtdSessoes = (int) (
                $dadosPost['qtdSessoes'] ?? 1
            );

            $intervaloDias = (int) (
                $dadosPost['intervaloDias'] ?? 7
            );

            if ($qtdSessoes < 1 || $qtdSessoes > 50) {
                $erros['qtdSessoes'] =
                    'A quantidade deve estar entre 1 e 50 sessões.';
            }

            if (!in_array($intervaloDias, [7, 14], true)) {
                $erros['intervaloDias'] =
                    'Selecione uma frequência válida.';
            }
        }

        if (!empty($erros)) {
            return $this->renderizarFormularioComErros(
                $dadosPost,
                $erros
            );
        }

        try {
            if ($entradaDto->id !== null) {
                $this->servico->remarcarSessao(
                    $entradaDto
                );

                $this->sessao->criarFlash(
                    'sucesso',
                    'Sessão remarcada com sucesso!'
                );
            } else {
                $qtdSessoes = (int) (
                    $dadosPost['qtdSessoes'] ?? 1
                );

                $intervaloDias = (int) (
                    $dadosPost['intervaloDias'] ?? 7
                );

                $this->servico->criarSessao(
                    $entradaDto,
                    $qtdSessoes,
                    $intervaloDias
                );

                $this->sessao->criarFlash(
                    'sucesso',
                    'Sessão criada com sucesso!'
                );
            }

            Helper::redirecionar('sessoes/listar');
        } catch (Throwable $erro) {
            return $this->renderizarFormularioComErros(
                $dadosPost,
                [
                    'global' => $erro->getMessage()
                ]
            );
        }
    }

    public function processarCancelamento(int $id): void
    {
        try {
            $tipoRecebido = filter_input(
                INPUT_POST,
                'tipoCancelamento',
                FILTER_UNSAFE_RAW
            );

            if (!is_string($tipoRecebido)) {
                throw new DomainException(
                    'Informe quem realizou o cancelamento.'
                );
            }

            $tipoCancelamento =
                TipoSessaoStatus::tryFrom($tipoRecebido);

            if (
                $tipoCancelamento
                !== TipoSessaoStatus::CANCELADA_PACIENTE
                && $tipoCancelamento
                !== TipoSessaoStatus::CANCELADA_PSICOLOGO
            ) {
                throw new DomainException(
                    'O tipo de cancelamento informado é inválido.'
                );
            }

            $cancelarSeguintes = filter_input(
                INPUT_POST,
                'cancelarSeguintes',
                FILTER_VALIDATE_BOOLEAN
            ) ?? false;

            $this->servico->cancelarSessao(
                $id,
                $tipoCancelamento,
                $cancelarSeguintes
            );

            $responsavel =
                $tipoCancelamento
                === TipoSessaoStatus::CANCELADA_PACIENTE
                ? 'pelo paciente'
                : 'pelo psicólogo';

            $mensagem = $cancelarSeguintes
                ? "Sessão e as seguintes foram canceladas "
                . "{$responsavel} com sucesso!"
                : "Sessão cancelada {$responsavel} com sucesso!";

            $this->sessao->criarFlash(
                'sucesso',
                $mensagem
            );
        } catch (Throwable $erro) {
            $this->sessao->criarFlash(
                'erro',
                $erro->getMessage()
            );
        }

        Helper::redirecionar('sessoes/listar');
    }

    private function renderizarFormularioComErros(
        array $dadosPost,
        array $erros
    ): string {
        return $this->template->renderizar(
            'Views/Sessao/formulario.html.twig',
            [
                'dados' => $dadosPost,
                'erros' => $erros,
                'pacientes' =>
                $this->pacienteRepositorio
                    ->listarPacienteEpsicologo()
            ]
        );
    }
}
