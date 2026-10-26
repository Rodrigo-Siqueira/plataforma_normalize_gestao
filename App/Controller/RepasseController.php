<?php

declare(strict_types=1);

namespace App\Controller;

use App\Dto\Input\RepasseInputDTO;
use App\Dto\Output\RepasseOutputDTO;
use App\Enum\TipoStatusRepasse;
use App\Repository\PsicologoRepository;
use App\Repository\RepasseRepository;
use App\Service\RepasseService;
use App\Validation\RepasseValidator;
use Normalize\Component\Mensagem;
use Normalize\Controller\BaseController;
use Normalize\Helper\Helper;
use Normalize\Session\Sessao;
use Override;
use Throwable;

class RepasseController extends BaseController
{
    private Sessao $sessao;
    private RepasseRepository $repositorio;
    private PsicologoRepository $psicologoRepositorio;
    private RepasseService $servico;

    #[Override]
    public function __construct()
    {
        parent::__construct(CAMINHO_TEMPLATE);
        $this->sessao = new Sessao();
        $this->repositorio = new RepasseRepository();
        $this->psicologoRepositorio = new PsicologoRepository();
        $this->servico = new RepasseService();
    }

    public function listar(): string
    {
        $totalItens = $this->repositorio->contarTotal();
        $totalAberto =  $this->repositorio->contarTotal(TipoStatusRepasse::ABERTO);
        $totalPago =  $this->repositorio->contarTotal(TipoStatusRepasse::PAGO);

        $paginacao = $this->montarPaginacao($totalItens);
        $dadosBrutos = $this->repositorio->listarPaginado($paginacao['itensPorPagina'], $paginacao['deslocamento']);
        $repassesPaginado = RepasseOutputDTO::criarColecao($dadosBrutos);

        $mensagemHtml = '';
        $flashSucesso = $this->sessao->obterFlash('sucesso');
        $flashErro = $this->sessao->obterFlash('erro');
        if ($flashSucesso) {
            $mensagemHtml = (string) (new Mensagem())->sucesso($flashSucesso);
        } elseif ($flashErro) {
            $mensagemHtml = (string) (new Mensagem())->erro($flashErro);
        }

        return $this->template->renderizar('Views/Repasse/listar.html.twig', [
            'repasses' => $repassesPaginado,
            'totalRepasses' => $totalItens,
            'totalAberto' => $totalAberto,
            'totalPago' => $totalPago,
            'mensagemFlash' => $mensagemHtml,
            'paginacao' => [
                'atual' => $paginacao['paginaAtual'],
                'total' => $paginacao['totalPaginas']
            ]
        ]);
    }

    private function montarPaginacao(int $totalItens): array
    {
        $itensPorPagina = 10;
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
        return $this->template->renderizar('Views/Repasse/formulario.html.twig', [
            'dados' => [],
            'erros' => [],
            'psicologos' => $this->psicologoRepositorio->listarPsicologosAtivo(),
            'sessoesDisponiveis' => $this->repositorio->listarSessoesDisponiveisParaRepasse(),

        ]);
    }

    public function processarCadastro(): string
    {
        $dadosPost = filter_input_array(INPUT_POST) ?? [];
        return $this->processarFormularioCadastro($dadosPost);
    }

    public function processarEdicao(int $id): string
    {
        $dadosPost = filter_input_array(INPUT_POST) ?? [];

        /*
        * O ID da URL é a referência confiável da edição.
        * Não dependemos exclusivamente do campo oculto.
        */
        $dadosPost['id'] = $id;

        return $this->processarFormularioEdicao($dadosPost);
    }

    private function processarFormularioEdicao(array $dadosPost): string {
        try {
            $entradaDto = RepasseInputDTO::criarDeArray($dadosPost);
        } catch (Throwable $erro) {
            return $this->renderizarEdicaoComErros(
                (int) ($dadosPost['id'] ?? 0),
                $dadosPost,
                [
                    'global' =>
                        'Os dados informados para o repasse são inválidos.'
                ]
            );
        }

        $validador = new RepasseValidator();
        $erros = $validador->validar($entradaDto);

        if ($erros !== []) {
            return $this->renderizarEdicaoComErros(
                $entradaDto->id ?? 0,
                $dadosPost,
                $erros
            );
        }

        try {
            $this->servico->alterarRepasse($entradaDto);
            $this->sessao->criarFlash(
                'sucesso',
                'Valor líquido do repasse atualizado com sucesso!'
            );

            Helper::redirecionar('repasses/listar');
        } catch (Throwable $erro) {
            return $this->renderizarEdicaoComErros(
                $entradaDto->id ?? 0,
                $dadosPost,
                [
                    'global' => $erro->getMessage()
                ]
            );
        }
    }

    private function renderizarEdicaoComErros(
        int $id,
        array $dadosPost,
        array $erros
    ): string {
        $dadosDoRepasse = $this->repositorio->buscarRepassePorId($id);

        if (!$dadosDoRepasse) {
            $this->sessao->criarFlash(
                'erro',
                'Repasse não encontrado.'
            );

            Helper::redirecionar('repasses/listar');
        }

        $repasse = RepasseOutputDTO::criarDeEntidade($dadosDoRepasse);

        return $this->template->renderizar(
            'Views/Repasse/editar.html.twig',
            [
                'repasse' => $repasse,
                'sessoesDoLote' =>
                $this->repositorio->listarSessoesDoLote($id),
                'dados' => $dadosPost,
                'erros' => $erros,
            ]
        );
    }

    public function processarRepassePago(int $id): void {
        try {
            $this->servico->marcarRepasseComoPago($id);

            $this->sessao->criarFlash(
                'sucesso',
                'Repasse marcado como pago com sucesso!'
            );
        } catch (Throwable $erro) {
            $this->sessao->criarFlash('erro', $erro->getMessage());
        }

        Helper::redirecionar('repasses/listar');
    }

    public function exibirFormularioEdicao(int $id): string
    {
        $dadosDoRepasse = $this->repositorio->buscarRepassePorId($id);

        if (!$dadosDoRepasse) {
            $this->sessao->criarFlash('erro', 'Repasse não encontrado.');
            Helper::redirecionar('repasses/listar');
        }

        $repasse = RepasseOutputDTO::criarDeEntidade($dadosDoRepasse);

        $sessoesDoLote = $this->repositorio->listarSessoesDoLote($id);

        return $this->template->renderizar(
            'Views/Repasse/editar.html.twig',
            [
                'repasse' => $repasse,
                'sessoesDoLote' => $sessoesDoLote,
                'dados' => [],
                'erros' => [],
            ]
        );
    }

    private function processarFormularioCadastro(array $dadosPost): string
    {
        try {
            $entradaDto = RepasseInputDTO::criarDeArray($dadosPost);
        } catch (Throwable $erro) {
            return $this->renderizarFormularioComErros(
                $dadosPost,
                [
                    'global' =>
                        'Os dados informados para o lote de repasse são inválidos.'
                ]
            );
        }

        $validador = new RepasseValidator();
        $erros = $validador->validar($entradaDto);

        if (!empty($erros)) {
            return $this->renderizarFormularioComErros($dadosPost, $erros);
        }

        try {
            $loteGeradoId = $this->servico
                ->criarLoteRepasse($entradaDto);

            $this->sessao->criarFlash(
                'sucesso',
                "Lote de repasse #{$loteGeradoId} criado com sucesso!"
            );

            Helper::redirecionar('repasses/listar');
        } catch (Throwable $erro) {
            return $this->renderizarFormularioComErros(
                $dadosPost,
                [
                    'global' => $erro->getMessage()
                ]
            );
        }
    }

    private function renderizarFormularioComErros(
        array $dadosPost,
        array $erros
    ): string {
        return $this->template->renderizar(
            'Views/Repasse/formulario.html.twig',
            [
                'dados' => $dadosPost,
                'erros' => $erros,
                'psicologos' => $this->psicologoRepositorio
                    ->listarPsicologosAtivo(),
                'sessoesDisponiveis' => $this->repositorio
                    ->listarSessoesDisponiveisParaRepasse(),
            ]
        );
    }
}
