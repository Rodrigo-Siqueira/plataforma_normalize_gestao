<?php

declare(strict_types=1);

namespace Normalize\Component;

/**
 * Classe Mensagem
 * * Componente responsável por gerenciar e renderizar mensagens de alerta (feedbacks)
 * utilizando as classes utilitárias do Bootstrap. Suporta interface fluida (fluent interface).
 * * @package Normalize\Component
 */

class Mensagem
{
    /**
     * @var string O conteúdo textual da mensagem (já sanitizado).
     */
    private string $texto;

    /**
     * @var string As classes CSS do Bootstrap aplicadas ao alerta.
     */
    private string $css;

    /**
     * Método mágico para conversão da classe em string.
     * * Permite que o objeto seja impresso diretamente (ex: echo $mensagem).
     *
     * @return string O HTML renderizado do alerta.
     */
    public function __toString()
    {
        return $this->renderizar();
    }

    /**
     * Define a mensagem como um alerta de sucesso.
     *
     * @param string $mensagem O texto do feedback de sucesso.
     * @return Mensagem
     */
    public function sucesso(string $mensagem): Mensagem
    {
        $this->css = 'alert alert-success alert-dismissible fade show shadow';
        $this->texto = $this->filtrar($mensagem);
        return $this;
    }

    /**
     * Define a mensagem como um alerta de erro/perigo.
     *
     * @param string $mensagem O texto do feedback de erro.
     * @return Mensagem
     */
    public function erro(string $mensagem): Mensagem
    {
        $this->css = 'alert alert-danger alert-dismissible fade show shadow';
        $this->texto = $this->filtrar($mensagem);
        return $this;
    }

    /**
     * Define a mensagem como um alerta de aviso (warning).
     *
     * @param string $mensagem O texto do feedback de aviso.
     * @return Mensagem
     */
    public function alerta(string $mensagem): Mensagem
    {
        $this->css = 'alert alert-warning alert-dismissible fade show shadow';
        $this->texto = $this->filtrar($mensagem);
        return $this;
    }

    /**
     * Define a mensagem como um alerta informativo.
     *
     * @param string $mensagem O texto do feedback informativo.
     * @return Mensagem
     */
    public function info(string $mensagem): Mensagem
    {
        $this->css = 'alert alert-primary alert-dismissible fade show shadow';
        $this->texto = $this->filtrar($mensagem);
        return $this;
    }

    /**
     * Renderiza o componente gerando o bloco HTML correspondente.
     *
     * @return string O código HTML completo do alerta estruturado para Bootstrap.
     */
    public function renderizar(): string
    {
        return "
        <div class='{$this->css}' role='alert'>
            <div class='d-flex align-items-center me-4'>
                {$this->texto}
            </div>
            <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
        </div>";
    }

    /**
     * Filtra e sanitiza o texto da mensagem para evitar falhas de XSS (Cross-Site Scripting).
     *
     * @param string $mensagem O texto bruto a ser sanitizado.
     * @return string O texto convertido em entidades HTML seguras.
     */
    private function filtrar(string $mensagem): string
    {
        return filter_var($mensagem, FILTER_SANITIZE_SPECIAL_CHARS);
    }
}
