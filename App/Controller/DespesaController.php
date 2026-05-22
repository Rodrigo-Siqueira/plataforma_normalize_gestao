<?php

namespace App\Controller;

use Normalize\Controller\BaseController;
use Override;

class DespesaController extends BaseController
{
    #[Override]
    public function __construct()
    {
        return parent::__construct(CAMINHO_TEMPLATE);
    }

    public function listar(): string
    {
        return $this->template->renderizar('Views/Despesa/listar.html.twig', []);
    }
}
