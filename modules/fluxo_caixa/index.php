<?php
$currentPage = 'fluxo_caixa';

include_once __DIR__ . '/../../includes/header.php';

$conn = mysqli_connect(
    'localhost',
    'root',
    '',
    'dev_agendamento_db'
) or die('Erro de conexão');

$sql = "SELECT
    v.data_pagamento as data,
    p.nome as descricao,
    pac.nome_completo as detalhe,
    v.valor_cobrado as valor,
    'VENDA' as tipo
FROM vendas v
JOIN produtos p ON v.produto_id = p.id
JOIN pacientes pac ON v.paciente_id = pac.id
WHERE v.status_pagamento = 'PAGO'";
$entradas =  mysqli_query($conn, $sql);

$sql = "SELECT
    desp.data_pagamento as data,
    desp.descricao as descricao,
    desp.categoria as detalhe,
    desp.valor as valor,
	'DESPESA' as tipo
FROM despesas_operacionais AS desp
WHERE desp.status = 'PAGO'";
$despesas =  mysqli_query($conn, $sql);

$sql = "SELECT 
	DATE_FORMAT(l.data_pagamento, '%Y-%m-%d') as data,
	p.nome_completo as descricao,
    CONCAT(DATE_FORMAT(l.periodo_inicio, '%d'), 
           ' à ', 
           DATE_FORMAT(l.periodo_fim, '%d-%m-%Y'))as detalhe,
    l.valor_liquido_pago as valor,
	'REPASSE' as tipo
FROM lotes_repasse AS l 
JOIN psicologo p ON p.id = l.psicologo_id
WHERE l.status = 'PAGO'";
$repasses =  mysqli_query($conn, $sql);


$fluxo_caixa  = [];


while ($row = mysqli_fetch_assoc($entradas)) $fluxo_caixa[] = $row;
while ($row = mysqli_fetch_assoc($repasses)) $fluxo_caixa[] = $row;
while ($row = mysqli_fetch_assoc($despesas)) $fluxo_caixa[] = $row;

usort($fluxo_caixa, function ($a, $b) {
    return strtotime($b['data']) <=> strtotime($a['data']);
});

$saldo = 0;
foreach ($fluxo_caixa as $mov) {
    if ($mov['tipo'] == 'VENDA') {
        $saldo += $mov['valor'];
    } else {
        $saldo -= $mov['valor'];
    }
}

foreach ($fluxo_caixa as &$mov) {
    $mov['saldo'] = $saldo;
    if ($mov['tipo'] == 'VENDA') {
        $saldo -= $mov['valor'];
    } else {
        $saldo += $mov['valor'];
    }
}

unset($mov);
?>

<div class="w3-container w3-padding  w3-margin-bottom">
    <h3>Fluxo de Caixa</h3>
    <div class="w3-card w3-white w3-padding w3-margin-top w3-round-large">
        <div class="w3-responsive ">
            <table class="w3-table-all w3-hoverable w3-striped w3-margin-top w3-margin-bottom">
                <thead>
                    <tr class="w3-theme-l1">
                        <td><b>DATA</b></td>
                        <td><b>DESCRIÇÃO</b></td>
                        <td><b>DETALHE</b></td>
                        <td><b>VALOR</b></td>
                        <td><b>TIPO</b></td>
                        <td><b>Saldo</b></td>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach ($fluxo_caixa as $mov) {
                        // valor formatado
                        $valorFormatado = "R$ " . number_format($mov['valor'], 2, ",", ".");
                        $saldoFormatado = "R$ " . number_format($mov['saldo'], 2, ",", ".");

                        // se for negativo, aplica a cor vermelha
                        if ($mov['tipo'] != 'VENDA') {
                            $valorFormatado = "<span style='color:red;'>- {$valorFormatado}</span>";
                        }

                        if ($mov['saldo'] < 0) {
                            $saldoFormatado = "<span style='color:red;'>{$saldoFormatado}</span>";
                        }

                        echo "<tr>
            <td>{$mov['data']}</td>
            <td>{$mov['descricao']}</td>
            <td>{$mov['detalhe']}</td>
            <td>{$valorFormatado}</td>
            <td>{$mov['tipo']}</td>
            <td>{$saldoFormatado}</td>
        </tr>";
                    }
                    mysqli_close($conn);
                    ?>
                </tbody>
            </table>
        </div>
        <br>
    </div>
</div>

<?php
include_once __DIR__ . '/../../includes/footer.php';
?>