<?php

namespace App\Controller;

use App\Dto\Input\VendaInputDTO;
use App\Dto\Output\VendaDetalheDTO;
use App\Dto\Output\VendaOutputDTO;
use App\Enum\TipoVendaStatus;
use App\Repository\PacienteRepository;
use App\Repository\ProdutoRepository;
use App\Repository\VendaRepository;
use App\Service\VendaService;
use App\Validation\VendaValidator;
use Exception;
use Normalize\Component\Mensagem;
use Normalize\Controller\BaseController;
use Normalize\Helper\Helper;
use Normalize\Session\Sessao;
use Override;

class VendaController extends BaseController
{
    private VendaRepository $repositorio;
    private Sessao $sessao;
    private PacienteRepository $pacienteRepositorio;
    private ProdutoRepository $produtoRepositorio;
    private VendaService $servico;

    #[Override]
    public function __construct()
    {
        parent::__construct(CAMINHO_TEMPLATE);
        $this->repositorio = new VendaRepository();
        $this->sessao = new Sessao();
        $this->servico = new VendaService();
        $this->pacienteRepositorio = new PacienteRepository();
        $this->produtoRepositorio = new ProdutoRepository();
    }

    public function listar(): string
    {
        $totalItens = $this->repositorio->contarTotal();
        $vendasPago = $this->repositorio->contarTotal(TipoVendaStatus::PAGO);
        $vendasCancelado = $this->repositorio->contarTotal(TipoVendaStatus::CANCELADO);

        $paginacao = $this->montarPaginacao($totalItens);
        $dadosBrutos = $this->repositorio->listarPaginado($paginacao['itensPorPagina'], $paginacao['deslocamento']);
        $vendasPaginado = VendaOutputDTO::criarColecao($dadosBrutos);

        $mensagemHtml = '';
        $flashSucesso = $this->sessao->obterFlash('sucesso');
        $flashErro = $this->sessao->obterFlash('erro');
        if ($flashSucesso) {
            $mensagemHtml = (string) (new Mensagem())->sucesso($flashSucesso);
        } elseif ($flashErro) {
            $mensagemHtml = (string) (new Mensagem())->erro($flashErro);
        }

        return $this->template->renderizar('Views/Venda/listar.html.twig', [
            'vendas' => $vendasPaginado,
            'totalVendas' => $totalItens,
            'vendasPago' => $vendasPago,
            'vendasCancelado' => $vendasCancelado,
            'mensagemFlash' => $mensagemHtml,
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

    public function exibirFormularioCadastro(): string {
        return $this->template->renderizar('Views/Venda/formulario.html.twig', [
            'pacientes' => $this->pacienteRepositorio->listarPacienteEresposavel(),
            'produtos' => $this->produtoRepositorio->listar()
        ]);
    }

    public function exibirFormularioEdicao(int $id) : string {
        $vendaObjeto = $this->repositorio->buscarVendaId($id);

        if (!$vendaObjeto) {
            Helper::redirecionar('vendas/listar');
        }

        $vendaOutput = VendaDetalheDTO::criarDeEntidade($vendaObjeto);
        
        return $this->template->renderizar('Views/Venda/formulario.html.twig', [
            'venda' => $vendaOutput,
            'pacientes' => $this->pacienteRepositorio->listarPacienteEresposavel(),
            'produtos' => $this->produtoRepositorio->listar()
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
        $entradaDto = VendaInputDTO::criarDeArray($dadosPost);
        $validador = new VendaValidator();
        $erros = $validador->validar($entradaDto);

        if (!empty($erros)) {
            return $this->template->renderizar(
                'Views/Venda/formulario.html.twig',
                [
                    'dados' => $dadosPost,
                    'erros' => $erros,
                    'pacientes' => $this->pacienteRepositorio->listarPacienteEresposavel(),
                    'produtos' => $this->produtoRepositorio->listar()
                ]
            );
        }

        try {
            $this->servico->processarVenda($entradaDto);

            $acao = !empty($dadosPost['id']) ? 'atualizada' : 'criada';
            $this->sessao->criarFlash('sucesso', "Venda {$acao} com sucesso!");

            Helper::redirecionar('vendas/listar');
        } catch (Exception $e) {
            return $this->template->renderizar('Views/Venda/formulario.html.twig', [
                'dados' => $dadosPost,
                'erros' => ['global' => $e->getMessage()],
                'pacientes' => $this->pacienteRepositorio->listarPacienteEresposavel(),
                'produtos' => $this->produtoRepositorio->listar()
            ]);
        }
    }
}
