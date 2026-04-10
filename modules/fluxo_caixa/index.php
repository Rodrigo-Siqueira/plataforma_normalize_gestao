<?php
require_once __DIR__ . '/../../config/config.php';
require_once 'regras_fluxo_caixa.php';

$entradas = listar_entradas_pago();
$despesas = listar_despesas_pago();
$repasses = listar_repasses_pago();

$fluxo_caixa = [];

# Combina todos os resultados
if (is_array($entradas) && isset($entradas[0])) {
    foreach ($entradas as $row) $fluxo_caixa[] = $row;
}

if (is_array($repasses) && isset($repasses[0])) {
    foreach ($repasses as $row) $fluxo_caixa[] = $row;
}

if (is_array($despesas) && isset($despesas[0])) {
    foreach ($despesas as $row) $fluxo_caixa[] = $row;
}

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

$currentPage = 'fluxo_caixa';
include_once __DIR__ . '/../../includes/header.php';
?>

<div class="w3-container w3-padding w3-margin-bottom">
    <h3>Fluxo de Caixa</h3>
    <div class="w3-card w3-white w3-padding w3-margin-top w3-round-large">
        <div class="w3-responsive">
            <table class="w3-table-all w3-hoverable w3-striped w3-margin-top w3-margin-bottom">
                <thead>
                    <tr class="w3-theme-l1">
                        <td><b>DATA</b></td>
                        <td><b>DESCRIÇÃO</b></td>
                        <td><b>DETALHE</b></td>
                        <td><b>VALOR</b></td>
                        <td><b>TIPO</b></td>
                        <td><b>SALDO</b></td>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($fluxo_caixa as $mov):
                        // Protege contra XSS
                        $descricao = htmlspecialchars($mov['descricao']);
                        $detalhe = htmlspecialchars($mov['detalhe']);
                        $valorFormatado = "R$ " . number_format($mov['valor'], 2, ",", ".");
                        $saldoFormatado = "R$ " . number_format($mov['saldo'], 2, ",", ".");
                        $dataFormatada = date('d/m/Y', strtotime($mov['data']));

                        // Aplica cor vermelha se negativo
                        if ($mov['tipo'] !== 'VENDA') {
                            $valorFormatado = "<span style='color:red;'>- {$valorFormatado}</span>";
                        }
                        if ($mov['saldo'] < 0) {
                            $saldoFormatado = "<span style='color:red;'>{$saldoFormatado}</span>";
                        }
                    ?>
                        <tr>
                            <td><?= $dataFormatada ?></td>
                            <td><?= $descricao ?></td>
                            <td><?= $detalhe ?></td>
                            <td><?= $valorFormatado ?></td>
                            <td><?= $mov['tipo'] ?></td>
                            <td><?= $saldoFormatado ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <br>
    </div>
</div>

<?php include_once __DIR__ . '/../../includes/footer.php'; ?>