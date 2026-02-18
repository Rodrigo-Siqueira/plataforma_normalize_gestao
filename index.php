<?php
// Adicniona arquivos com as Constantes
require_once __DIR__ . '/config/config.php';
require_once ROOT_PATH . '/modules/dashboard/regras_dashboard.php';
require_once ROOT_PATH . '/modules/dashboard/tabelas_dashboard.php';

/* --- Carrega os dados --- */
$faturamento_mes = faturamento_total_mes();
$pacientes_ativos = contar_pacientes_ativos();
$repasses_pendentes = contar_repasses_pendentes();
$sessoes_hoje = sessoes_realizadas_hoje();
$sessoes_proximas = listar_sessoes_proximas(1) ?: [];
$renovacoes = listar_pacientes_para_renovar(2);
$sessoes_psicologo = sessoes_por_psicologo_mes();
$fluxo_meses = fluxo_financeiro_ultimos_meses(6);

// Nome da página e inclui o cabeçalho
$currentPage = 'dashboard';
include_once ROOT_PATH . '/includes/header.php';
// Variável recebe o caminho do template
$dashboard = TEMPLATE_PATH . 'dashboard.html';

if (file_exists($dashboard)) {

    $pagina = file_get_contents($dashboard);
    $pagina = str_replace('{FATURAMENTO}', htmlspecialchars(fmt_money($faturamento_mes)), $pagina);
    $pagina = str_replace('{PACIENTES_ATIVOS}', (int)$pacientes_ativos, $pagina);
    $pagina = str_replace('{REPASSES_PENDENTES}', (int)$repasses_pendentes, $pagina);
    $pagina = str_replace('{SESSOES_HOJE}', (int)$sessoes_hoje, $pagina);
    $pagina = str_replace('{PROX_SESSOES}', tabela_sessoes_proximas($sessoes_proximas), $pagina);
    $pagina = str_replace('{RENOVACOES}', tabela_renovacoes($renovacoes), $pagina);
    $pagina = str_replace('{SESSOES_PSICOLOGOS}', tabela_sessoes_psicologos($sessoes_psicologo), $pagina);
    $pagina = str_replace('{FLUXO_MES}', json_encode($fluxo_meses, JSON_UNESCAPED_UNICODE), $pagina);

    print $pagina;
} else {
    print "Template não encontrado.";
}

include_once ROOT_PATH . '/includes/footer.php';