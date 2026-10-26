<?php

declare(strict_types=1);

namespace Normalize\Component;

use Normalize\Helper\Helper;
use Twig\Environment;
use Twig\Extension\DebugExtension;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFunction;

/**
 * Classe Template
 *
 * Componente responsável por gerenciar a renderização de views utilizando o motor
 * de templates Twig. Configura automaticamente o ambiente de desenvolvimento/produção
 * e registra funções personalizadas globais para as views.
 *
 * @package Normalize\Component
 */

class Template
{
    /**
     * @var Environment Instância do ambiente do Twig configurada.
     */
    protected Environment $twig;

    /**
     * Construtor da classe Template.
     *
     * Inicializa o carregador de arquivos, detecta o ambiente atual do sistema
     * para ativar ou desativar o modo de depuração (debug) e registra as funções auxiliares.
     *
     * @param string $caminhoTemplate Caminho do diretório onde as views (.twig) estão localizadas.
     */
    public function __construct(string $caminhoTemplate)
    {
        $loader = new FilesystemLoader($caminhoTemplate);

        $isDev = Helper::identifarAmbiente();

        $opcoesEnviroment = [
            'debug' => $isDev,
            'strict_variables' => $isDev
        ];

        $this->twig = new Environment($loader, $opcoesEnviroment);

        if ($isDev) {
            $this->twig->addExtension(new DebugExtension());
        }

        $this->registrarFuncoes();
    }

    /**
     * Renderiza um arquivo de view do Twig injetando os dados passados.
     *
     * @param string $view O nome ou caminho do arquivo de view (ex: 'home.html.twig').
     * @param array $dados Dados/variáveis associativos que serão disponibilizados dentro da view.
     * @return string O código HTML/conteúdo renderizado final.
     * * @throws \Twig\Error\LoaderError Se o arquivo do template não for encontrado.
     * @throws \Twig\Error\RuntimeError Se ocorrer um erro durante a execução do template.
     * @throws \Twig\Error\SyntaxError Se houver um erro de sintaxe no código Twig.
     */
    public function renderizar(string $view, array $dados = []): string
    {
        return $this->twig->render($view, $dados);
    }

    /**
     * Registra funções personalizadas (custom functions) dentro do ambiente Twig.
     *
     * Permite que métodos utilitários, como o `Helper::url()`, sejam chamados diretamente 
     * de dentro dos arquivos de template (ex: {{ url('css/estilo.css') }}).
     *
     * @return void
     */
    private function registrarFuncoes(): void
    {
        $this->twig->addFunction(
            new TwigFunction('url', fn(string $url) => Helper::url($url))
        );
    }

}