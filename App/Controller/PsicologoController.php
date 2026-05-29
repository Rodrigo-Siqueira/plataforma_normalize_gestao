<?php

namespace App\Controller;

use App\Dto\Input\PsicologoInputDTO;
use App\Repository\PsicologoRepository;
use App\Service\PsicologoService;
use App\Validation\PsicologoValidator;
use Exception;
use Normalize\Component\Mensagem;
use Normalize\Controller\BaseController;
use Normalize\Helper\Helper;
use Normalize\Session\Sessao;
use Override;

class PsicologoController extends BaseController
{
    private PsicologoRepository $repositorio;
    private PsicologoService $servico;
    private Sessao $sessao;

    #[Override]
    public function __construct()
    {
        parent::__construct(CAMINHO_TEMPLATE);
        $this->repositorio = new PsicologoRepository();
        $this->servico = new PsicologoService();
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
        
        return $this->template->renderizar('Views/Psicologo/listar.html.twig', 
        [
            'psicologos' => $this->repositorio->listar(),
            'mensagemFlash' => $mensagemHtml
        ]);
    }

    public function exibirFormularioCadastro(): string
    {
        return $this->template->renderizar('Views/Psicologo/formulario.html.twig');
    }

    public function exibirFormularioEdicao(int $id): string
    {
        $psicologOutPut = $this->repositorio->buscarPorId($id);

        if (!$psicologOutPut) {
            Helper::redirecionar('psicologos/listar');
        }

        return $this->template->renderizar(
            'Views/Psicologo/formulario.html.twig',[
                'psicologo' => $psicologOutPut
            ]
        );
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
        $entradaDto = PsicologoInputDTO::criarDeArray($dadosPost);
        $validador = new PsicologoValidator();
        $erros = $validador->validar($entradaDto);

        if (!empty($erros)) {
            return $this->template->renderizar('Views/Psicologo/formulario.html.twig', [
                'dados' => $dadosPost,
                'erros' => $erros
            ]);
        }

        try {
            $this->servico->salvar($entradaDto);

            $acao = isset($dadosPost['id']) ? 'atualizado' : 'criado';
            $this->sessao->criarFlash('sucesso', "Psicólogo {$acao} com sucesso!");

            Helper::redirecionar('psicologos/listar');
        } catch (Exception $e) {
            $erros['global'] = $e->getMessage();

            return $this->template->renderizar('Views/Psicologo/formulario.html.twig', [
                'dados' => $dadosPost,
                'erros' => $erros
            ]);
        }
    }

    public function excluir(int $id)
    {
        try {
            $this->servico->excluir($id);
            $this->sessao->criarFlash('sucesso', 'Psicologo desativado com sucesso!');
        } catch (Exception $e) {
            $this->sessao->criarFlash('erro', 'Erro ao desativar psicologo: ' . $e->getMessage());
        }
        
        Helper::redirecionar('psicologos/listar');
    }
}