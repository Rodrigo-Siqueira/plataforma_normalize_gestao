<?php

namespace App\Controller;

use Normalize\Controller\BaseController;
use Override;

class ErroController extends BaseController
{
    #[Override]
    public function __construct()
    {
        parent::__construct(CAMINHO_TEMPLATE);
    }

    public function erro404(): string
    {
        return $this->template->renderizar('/Errors/404.html.twig',[
            'titulo' => 'Página não encontrada'
        ]);
    }
}