<?php

namespace App\Controller;

use App\Repository\DashboardRepository;
use Normalize\Controller\BaseController;
use Normalize\Helper\Helper;
use Override;

class DashboardController extends BaseController
{
    #[Override]
    public function __construct()
    {
        parent::__construct(CAMINHO_TEMPLATE);
    }

    public function mostrar(): string
    {
        $dashboard = new DashboardRepository;

        return $this->template->renderizar('Views/dashboard.html.twig', 
        [
            'faturamentoMes' => Helper::formatarMoeda($dashboard->faturamentoTotalMes()),
            'pacientesAtivos' => $dashboard->contarPacientesAtivos(),
            'repassesPendentes' => $dashboard->contarRepassesPendentes(),
            'sessoesHoje' => $dashboard->sessoesRealizadasHoje(),
            'proxSessoes' => $dashboard->listarSessoesProximas(2),
            'renovacoes' => $dashboard->listarPacientesParaRenovar(),
            'sessoesPsicologos' => $dashboard->sessoesPorPsicologoMes(),
            'fluxoMes' => $dashboard->fluxoFinancUltimosSeisMeses(),
            'titulo' => 'Conteúdo da Dashboard'
        ]);
    }
}
