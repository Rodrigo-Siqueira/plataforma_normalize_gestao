<?php

declare(strict_types=1);

namespace App\Controller;

use App\Dto\Input\ProdutoInputDTO;
use App\Repository\ProdutoRepository;
use App\Service\ProdutoService;
use App\Validation\ProdutoValidator;
use Normalize\Controller\BaseController;
use Normalize\Helper\Helper;
use Normalize\Session\Sessao;
use Normalize\Component\Mensagem;
use Exception;
use Override;

class ProdutoController extends BaseController
{
    private ProdutoRepository $repositorio;
    private ProdutoService $servico;
    private Sessao $sessao;

    #[Override]
    public function __construct()
    {
        parent::__construct(CAMINHO_TEMPLATE);

        $this->repositorio = new ProdutoRepository();
        $this->servico = new ProdutoService();
        $this->sessao = new Sessao();
    }

    public function listar(): string
    {
        $mensagemHtml = '';

        $flashSucesso = $this->sessao->obterFlash('sucesso');
        $flashErro = $this->sessao->obterFlash('erro');
        if ($flashSucesso) {
            $mensagemHtml = (string) (new Mensagem())->sucesso($flashSucesso);
        } elseif ($flashErro) {
            $mensagemHtml = (string) (new Mensagem())->erro($flashErro);
        }
        
        return $this->template->renderizar('Views/Produto/listar.html.twig', 
        [
            'produtos' => $this->repositorio->listar(),
            'mensagemFlash' => $mensagemHtml
        ]);
    }

    public function exibirFormularioCadastro(): string
    {
        return $this->template->renderizar('Views/Produto/formulario.html.twig');
    }

    public function exibirFormularioEdicao(int $id): string
    {
        $produtoOutput = $this->repositorio->buscarPorId($id);

        if (!$produtoOutput) {
            Helper::redirecionar('produtos/listar');
        }

        return $this->template->renderizar('Views/Produto/formulario.html.twig', [
            'produto' => $produtoOutput
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
        $entradaDto = ProdutoInputDTO::criarDeArray($dadosPost);
        $validador = new ProdutoValidator();
        $erros = $validador->validar($entradaDto);

        if (!empty($erros)) {
            return $this->template->renderizar('Views/Produto/formulario.html.twig',
            [
                'dados' => $dadosPost,
                'erros' => $erros
            ]);
        }

        try {
            $this->servico->salvar($entradaDto);

            $acao = isset($dadosPost['id']) ? 'atualizado' : 'criado';
            $this->sessao->criarFlash('sucesso', "Produto {$acao} com sucesso!");

            Helper::redirecionar('produtos/listar');
        } catch (Exception $e) {
            return $this->template->renderizar('Views/Produto/formulario.html.twig', [
                'dados' => $dadosPost,
                'erros' => ['global' => $e->getMessage()]
            ]);
        }
    }

    public function excluir(int $id): void
    {
        try {
            $this->servico->excluir($id);
            $this->sessao->criarFlash('sucesso', 'Produto desativado com sucesso!');
        } catch (Exception $e) {
            $this->sessao->criarFlash('erro', 'Erro ao desativar produto: ' . $e->getMessage());
        }

        Helper::redirecionar('produtos/listar');
    }
}
