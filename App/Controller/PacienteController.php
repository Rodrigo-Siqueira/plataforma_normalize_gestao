<?php

namespace App\Controller;

use Normalize\Controller\BaseController;
use Override;

class PacienteController extends BaseController
{
    #[Override]
    public function __construct()
    {
        return parent::__construct(CAMINHO_TEMPLATE);
    }

    public function listar(): string
    {
        return $this->template->renderizar('Views/Paciente/listar.html.twig', []);
    }
}