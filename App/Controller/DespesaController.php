<?php

namespace App\Controller;

use App\Dto\Input\DespesaInputDTO;
use App\Dto\Output\DespesaOutputDTO;
use App\Enum\TipoStatusDespesa;
use App\Repository\DespesaRepository;
use App\Service\DespesaService;
use App\Validation\DespesaValidator;
use Exception;
use Normalize\Component\Mensagem;
use Normalize\Controller\BaseController;
use Normalize\Helper\Helper;
use Normalize\Session\Sessao;
use Override;

class DespesaController extends BaseController
{
    private DespesaRepository $repositorio;
    private DespesaService $servico;
    private Sessao $sessao;

    #[Override]
    public function __construct()
    {
        parent::__construct(CAMINHO_TEMPLATE);

        $this->repositorio = new DespesaRepository();
        $this->servico = new DespesaService();
        $this->sessao = new Sessao();
    }

    public function listar(): string
    {
        $totalItens = $this->repositorio->contarTotal();
        $despesasPendente = $this->repositorio->contarTotal(TipoStatusDespesa::PENDENTE);
        $despesasPago = $this->repositorio->contarTotal(TipoStatusDespesa::PAGO);

        $paginacao = $this->montarPaginacao($totalItens);
        $dadosBrutos = $this->repositorio->listarPaginando($paginacao['itensPorPagina'], $paginacao['deslocamento']);
        $despesasPaginado = DespesaOutputDTO::criarColecao($dadosBrutos);

        $mensagemHtml = '';
        $flashSucesso = $this->sessao->obterFlash('sucesso');
        $flashErro = $this->sessao->obterFlash('erro');
        if ($flashSucesso) {
            $mensagemHtml = (string) (new Mensagem())->sucesso($flashSucesso);
        } elseif ($flashErro) {
            $mensagemHtml = (string) (new Mensagem())->erro($flashErro);
        }

        return $this->template->renderizar('Views/Despesa/listar.html.twig', [
            'despesas' => $despesasPaginado,
            'mensagemFlash' => $mensagemHtml,
            'totalDespesas' => $totalItens,
            'despesasPendente' => $despesasPendente,
            'despesasPago' => $despesasPago,
            'paginacao' => [
                'atual' => $paginacao['paginaAtual'],
                'total' => $paginacao['totalPaginas']
            ]
        ]);
    }

    private function montarPaginacao(int $totalItens): array
    {
        $itensPorPagina = 5;
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
        return $this->template->renderizar('Views/Despesa/formulario.html.twig');
    }

    public function exibirFormularioEdicao(int $id): string
    {
        $dadosBruto = DespesaOutputDTO::criarDeEntidade($this->repositorio->buscarPorId($id));
        $despesaOutput = $dadosBruto;

        if (!$despesaOutput) {
            Helper::redirecionar('despesas/listar');
        }

        return $this->template->renderizar('Views/Despesa/formulario.html.twig', [
            'despesa' => $despesaOutput
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

    private function processarFormulario(array $dadosPost): string
    {
        $entradaDto = DespesaInputDTO::criarDeArray($dadosPost);
        $validador = new DespesaValidator();
        $erros = $validador->validar($entradaDto);

        if (!empty($erros)) {
            return $this->template->renderizar(
                'Views/Despesa/formulario.html.twig',
                [
                    'dados' => $dadosPost,
                    'erros' => $erros
                ]
            );
        }

        try {
            $this->servico->salvar($entradaDto);

            $acao = !empty($dadosPost['id']) ? 'atualizado' : 'criado';
            $this->sessao->criarFlash('sucesso', "Despesa {$acao} com sucesso!");

            Helper::redirecionar('despesas/listar');
        } catch (Exception $e) {
            return $this->template->renderizar('Views/Despesa/formulario.html.twig', [
                'dados' => $dadosPost,
                'erros' => ['global' => $e->getMessage()]
            ]);
        }
    }
}
