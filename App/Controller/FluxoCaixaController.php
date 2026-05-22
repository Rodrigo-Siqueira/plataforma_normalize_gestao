<?php

namespace App\Controller;

use App\Model\FluxoCaixaModel;
use Normalize\Controller\BaseController;
use Override;

class FluxoCaixaController extends BaseController
{
    #[Override]
    public function __construct()
    {
        return parent::__construct(CAMINHO_TEMPLATE);
    }

    public function listar(): string
    {
        $fluxo = new FluxoCaixaModel();

        $fluxoCaixa = array_merge(
            $fluxo->listarEntradasPago(),
            $fluxo->listarDespesasPago(),
            $fluxo->listarRepassesPago()
        );

        usort($fluxoCaixa, function ($a, $b) {
            return strtotime($b->data) <=> strtotime($a->data);
        });

        $saldo = 0;
        foreach ($fluxoCaixa as $mov) {
            if ($mov->tipo == 'VENDA') {
                $saldo += $mov->valor;
            } else {
                $saldo -= $mov->valor;
            }
        }

        foreach ($fluxoCaixa as &$mov) {
            $mov->saldo = $saldo;
            if ($mov->tipo == 'VENDA') {
                $saldo -= $mov->valor;
            } else {
                $saldo += $mov->valor;
            }
        }
        unset($mov);

        return $this->template->renderizar('Views/FluxoCaixa/listar.html.twig', 
        [
            'fluxoCaixa' => $fluxoCaixa
        ]);
    }
}
