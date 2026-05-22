<?php

namespace App\Controller;

use Normalize\Controller\BaseController;
use Override;

class PsicologoController extends BaseController
{
    #[Override]
    public function __construct()
    {
        return parent::__construct(CAMINHO_TEMPLATE);
    }

    public function listar(): string
    {
        $mensagemHtml = '';
        return $this->template->renderizar('Views/Psicologo/listar.html.twig', 
        [
            'psicologos' => [],
            'mensagemFlash' => $mensagemHtml
        ]);
    }
}