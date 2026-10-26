<?php

declare(strict_types=1);

namespace Normalize\Controller;

use Normalize\Component\Template;

/**
 * Classe Abstrata BaseController
 *
 * Serve como fundação para todos os controladores (Controllers) do sistema.
 * Garante que qualquer controller filho tenha acesso nativo ao componente 
 * de renderização de templates.
 *
 * @package Normalize\Controller
 * @abstract
 */
abstract class BaseController
{
    /**
     * @var Template Instância do componente de renderização de templates (Twig).
     */
    protected Template $template;

    /**
     * Construtor do BaseController.
     *
     * Inicializa o motor de templates definindo o diretório base onde
     * as views do controller específico serão localizadas.
     *
     * @param string $caminhoTemplate Caminho completo até o diretório de templates.
     */
    public function __construct(string $caminhoTemplate)
    {
        $this->template = new Template($caminhoTemplate);   
    }
}