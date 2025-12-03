<?php
require_once 'regras_dashboard.php';


/* --- Carrega os dados --- */
$faturamento_mes = faturamento_total_mes();
$pacientes_ativos = contar_pacientes_ativos();
$repasses_pendentes = contar_repasses_pendentes();
$sessoes_hoje = sessoes_realizadas_hoje();
$sessoes_proximas = listar_sessoes_proximas(1);
$renovacoes = listar_pacientes_para_renovar(2);
$sessoes_psicologo = sessoes_por_psicologo_mes();
$fluxo_meses = fluxo_financeiro_ultimos_meses(6);


$currentPage = 'dashboard';
include_once 'includes/header.php';
?>

<!-- Header -->
<header class="w3-container" style="padding-top:22px">
    <h5><b><i class="fa fa-dashboard"></i> Painel Geral</b></h5>
</header>

<div class="w3-row-padding w3-margin-bottom">
    <!-- Card 1 -->
    <div class="w3-quarter">
        <div class="w3-container w3-teal w3-padding-16">
            <div class="w3-left">
                <span class="material-symbols-outlined">attach_money</span>
            </div>
            <div class="w3-right">
                <h3><?= htmlspecialchars(fmt_money($faturamento_mes)) ?></h3>
            </div>
            <div class="w3-clear"></div>
            <h4>Faturamento (mês)</h4>
        </div>
    </div>

    <!-- Card 2 -->
    <div class="w3-quarter">
        <div class="w3-container w3-blue w3-padding-16">
            <div class="w3-left">
                <span class="material-symbols-outlined">personal_injury</span>
            </div>
            <div class="w3-right">
                <h3><?= (int)$pacientes_ativos ?></h3>
            </div>
            <div class="w3-clear"></div>
            <h4>Pacientes ativos</h4>
        </div>
    </div>

    <!-- Card 3 -->
    <div class="w3-quarter">
        <div class="w3-container w3-orange w3-text-white w3-padding-16">
            <div class="w3-left">
                <span class="material-symbols-outlined">currency_exchange</span>
            </div>
            <div class="w3-right">
                <h3><?= (int)$repasses_pendentes ?></h3>
            </div>
            <div class="w3-clear"></div>
            <h4>Repasses pendentes</h4>
        </div>
    </div>

    <!-- Card 4 -->
    <div class="w3-quarter">
        <div class="w3-container w3-red w3-padding-16">
            <div class="w3-left">
                <span class="material-symbols-outlined">calendar_month</span>
            </div>
            <div class="w3-right">
                <h3><?= (int)$sessoes_hoje ?></h3>
            </div>
            <div class="w3-clear"></div>
            <h4>Sessões realizadas hoje</h4>
        </div>
    </div>
</div>
<hr>
<div class="w3-container">
    <div class="w3-card w3-white w3-padding w3-margin-top w3-round-large">
        <h4>Próximas sessões (hoje e amanhã)</h4>
        <div class="w3-responsive">
            <table class="w3-table-all w3-hoverable w3-margin-bottom">
                <thead>
                    <tr class="w3-theme-l1">
                        <th>Data</th>
                        <th>Hora</th>
                        <th>Paciente</th>
                        <th>Psicólogo</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($sessoes_proximas)): ?>
                        <tr>
                            <td colspan="5" class="w3-center">Nenhuma sessão agendada.</td>
                        </tr>
                        <?php else: foreach ($sessoes_proximas as $s): ?>
                            <tr>
                                <td><?= date('d/m/Y', strtotime($s['data_agendada'])) ?></td>
                                <td><?= htmlspecialchars($s['hora_agendada']) ?></td>
                                <td><?= htmlspecialchars($s['paciente']) ?></td>
                                <td><?= htmlspecialchars($s['psicologo']) ?></td>
                                <td><?= htmlspecialchars($s['status']) ?></td>
                            </tr>
                    <?php endforeach;
                    endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<hr>
<div class="w3-container">
    <div class="w3-card w3-white w3-padding w3-margin-top w3-round-large">
        <h4>Pacientes para renovar (até 2 sessões restantes)</h4>
        <div class="w3-responsive">
            <table class="w3-table-all w3-hoverable w3-margin-bottom">
                <thead>
                    <tr class="w3-theme-l1">
                        <th>Paciente</th>
                        <th>Responsável</th>
                        <th>Pacote</th>
                        <th>Realizadas</th>
                        <th>Restantes</th>
                        <th>Última sessão</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($renovacoes)): ?>
                        <tr>
                            <td colspan="6" class="w3-center">Nenhum paciente precisa renovar agora.</td>
                        </tr>
                        <?php else: foreach ($renovacoes as $r): ?>
                            <tr>
                                <td><?= htmlspecialchars($r['paciente']) ?></td>
                                <td><?= htmlspecialchars($r['psicologo']) ?></td>
                                <td><?= htmlspecialchars($r['produto']) ?></td>
                                <td><?= (int)$r['sessoes_realizadas'] ?></td>
                                <td><?= (int)$r['sessoes_restantes'] ?></td>
                                <td><?= $r['ultima_sessao'] ? date('d/m/Y', strtotime($r['ultima_sessao'])) : '-' ?></td>
                            </tr>
                    <?php endforeach;
                    endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<hr>

<div class="w3-container">
    <div class="w3-card w3-white w3-padding w3-margin-top w3-round-large">
        <h4>Fluxo financeiro (últimos 6 meses)</h4>
        <canvas id="fluxoChart" style="width:100%;height:300px;"></canvas>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            (function() {
                const dados = <?= json_encode($fluxo_meses, JSON_UNESCAPED_UNICODE) ?>;
                const labels = dados.map(d => d.month_label);
                const vendas = dados.map(d => parseFloat(d.total_vendas) || 0);
                const despesas = dados.map(d => parseFloat(d.total_despesas) || 0);
                const repasses = dados.map(d => parseFloat(d.total_repasses) || 0);

                const ctx = document.getElementById('fluxoChart').getContext('2d');
                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                                label: 'Vendas',
                                data: vendas,
                                stack: 'stack1'
                            },
                            {
                                label: 'Despesas',
                                data: despesas,
                                stack: 'stack1'
                            },
                            {
                                label: 'Repasses',
                                data: repasses,
                                stack: 'stack1'
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                position: 'top'
                            }
                        },
                        scales: {
                            y: {
                                title: {
                                    display: true,
                                    text: 'R$'
                                }
                            }
                        }
                    }
                });
            })();
        </script>
    </div>
</div>
<hr>
<div class="w3-container">
    <div class="w3-card w3-white w3-padding w3-margin-top w3-round-large">
        <h4>Sessões realizadas por psicólogo (mês)</h4>
        <div class="w3-responsive">
            <table class="w3-table-all w3-hoverable w3-margin-bottom">
                <thead>
                    <tr class="w3-theme-l1">
                        <th>Psicólogo</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($sessoes_psicologo)): ?>
                        <tr>
                            <td colspan="2" class="w3-center">Nenhuma sessão registrada este mês.</td>
                        </tr>
                        <?php else: foreach ($sessoes_psicologo as $row): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['psicologo']) ?></td>
                                <td><?= (int)$row['total'] ?></td>
                            </tr>
                    <?php endforeach;
                    endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<hr>

<?php
include_once 'includes/footer.php';
?>