<?php

namespace App\Controller;

use App\Dto\Input\PacienteInputDTO;
use App\Dto\Output\PacienteOutputDTO;
use App\Dto\Output\ResponsavelPacienteOutputDTO;
use App\Repository\PacienteRepository;
use App\Repository\PsicologoRepository;
use App\Repository\ResponsavelPacienteRepository;
use App\Service\PacienteService;
use App\Validation\PacienteValidator;
use Normalize\Controller\BaseController;
use Normalize\Helper\Helper;
use Normalize\Session\Sessao;
use Override;
use Exception;
use Normalize\Component\Mensagem;

class PacienteController extends BaseController
{
    private PacienteRepository $pacienteRepositorio;
    private ResponsavelPacienteRepository $responsavelRepositorio;
    private PsicologoRepository $psicologoRepositorio;
    private PacienteService $servico;
    private Sessao $sessao;

    #[Override]
    public function __construct()
    {
        parent::__construct(CAMINHO_TEMPLATE);
        $this->servico = new PacienteService();
        $this->pacienteRepositorio = new PacienteRepository();
        $this->responsavelRepositorio = new ResponsavelPacienteRepository();
        $this->psicologoRepositorio = new PsicologoRepository();
        $this->sessao = new Sessao();
    }

    public function listar(): string
    {
        $totalItens = $this->pacienteRepositorio->contarTotal();
        $pacientesAtivos = $this->pacienteRepositorio->contarTotal(1);
        $pacientesInativos = $totalItens - $pacientesAtivos;

        $paginacao = $this->montarPaginacao($totalItens);
        $dadosBrutos = $this->pacienteRepositorio->listarPaginando($paginacao['itensPorPagina'], $paginacao['deslocamento']);
        $pacientesPaginados = PacienteOutputDTO::criarColecao($dadosBrutos);
        $mensagemHtml = '';

        $flashSucesso = $this->sessao->obterFlash('sucesso');
        $flashErro = $this->sessao->obterFlash('erro');
        if ($flashSucesso) {
            $mensagemHtml = (string) (new Mensagem())->sucesso($flashSucesso);
        } elseif ($flashErro) {
            $mensagemHtml = (string) (new Mensagem())->erro($flashErro);
        }
        return $this->template->renderizar('Views/Paciente/listar.html.twig', [
            'pacientes' => $pacientesPaginados,
            'totalPacientes' => $totalItens,
            'pacientesAtivos' => $pacientesAtivos,
            'pacientesInativos' => $pacientesInativos,
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

    public function exibirFormularioCadastro(): string
    {
        $psicologos = $this->psicologoRepositorio->listarPsicologosAtivo();
        return $this->template->renderizar('Views/Paciente/formulario.html.twig', [
            'psicologos' => $psicologos
        ]);
    }

    public function exibirFormularioEdicao(int $id): string
    {
        $dadosPaciente = $this->pacienteRepositorio->buscarPorId($id);

        if (!$dadosPaciente) {
            Helper::redirecionar('pacientes/listar');
        }

        $dadosResponsavel = $this->responsavelRepositorio->buscarPorPacienteId($id);
        $responsavelDTO = null;
        // Só cria o DTO se houver registro e o vínculo NÃO for próprio
        if ($dadosResponsavel && $dadosResponsavel->vinculo !== 'PROPRIO') {
            $responsavelDTO = ResponsavelPacienteOutputDTO::criarDeEntidade($dadosResponsavel);
        }

        $dadosCompletos = PacienteOutputDTO::criarDeEntidade($dadosPaciente, $responsavelDTO);
        $psicologos = $this->psicologoRepositorio->listarPsicologosAtivo();
        return $this->template->renderizar('Views/Paciente/formulario.html.twig', [
            'paciente' => $dadosCompletos,
            'psicologos' => $psicologos
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

    private function processarFormulario(array $dadosPost)
    {
        $entradaDto = PacienteInputDTO::criarDeArray($dadosPost);
        $validador = new PacienteValidator();
        $erros = $validador->validar($entradaDto);

        $psicologos = $this->psicologoRepositorio->listarPsicologosAtivo();
        if (!empty($erros)) {
            return $this->template->renderizar('Views/Paciente/formulario.html.twig', [
                'dados' => $dadosPost,
                'erros' => $erros,
                'psicologos' => $psicologos
            ]);
        }

        try {
            $this->servico->salvar($entradaDto);

            $acao = isset($dadosPost['id']) ? 'atualizado' : 'criado';
            $this->sessao->criarFlash('sucesso', "Paciente {$acao} com sucesso!");

            Helper::redirecionar('pacientes/listar');
        } catch (Exception $e) {
            $erros['global'] = $e->getMessage();

            return $this->template->renderizar('Views/Paciente/formulario.html.twig', [
                'dados' => $dadosPost,
                'erros' => $erros,
                'psicologos' => $psicologos
            ]);
        }
    }

    public function excluir(int $id): void
    {
        try {
            $this->servico->excluir($id);
            $this->sessao->criarFlash('sucesso', 'Paciente desativado com sucesso!');
        } catch (Exception $e) {
            $this->sessao->criarFlash('erro', 'Erro ao desativar paciente: ' . $e->getMessage());
        }

        Helper::redirecionar('pacientes/listar');
    }
}
