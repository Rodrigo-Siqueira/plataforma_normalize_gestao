<?php
$currentPage = 'vendas';

include_once __DIR__ . '/../../includes/header.php';

$conn = mysqli_connect(
    'localhost',
    'root',
    '',
    'dev_agendamento_db'
) or die('Erro de conexão');

if (!empty($_GET['action']) and $_GET['action'] == 'delete') {
    $id = $_GET['id'];

    $sql = "DELETE FROM vendas WHERE id = '{$id}'";
    mysqli_query($conn, $sql);
}

$sql = "
    SELECT
	v.id,
    pa.nome_completo,
    p.nome,
    v.valor_cobrado,
    v.data_venda,
    v.status_pagamento,
    v.data_pagamento

FROM vendas AS v
JOIN pacientes AS pa ON pa.id = v.paciente_id
JOIN produtos AS p ON p.id = v.produto_id
ORDER BY v.id";

$result  = mysqli_query($conn, $sql);

?>

<div class="w3-container w3-padding  w3-margin-bottom">
    <h3>Gestão de Vendas</h3>
    <div class="w3-card w3-white w3-padding w3-margin-top w3-round-large">
        <div class="w3-row w3-margin-bottom  w3-margin-top">
            <div class="w3-col s12 m12 l12 w3-right-align">
                <a href="venda_form.php" class="w3-button w3-green w3-round">
                    <span class="material-symbols-outlined">add</span>
                    <b>Nova Vendas</b>
                </a>
            </div>
        </div>
        <div class="w3-responsive ">
            <table class="w3-table-all w3-hoverable w3-striped w3-margin-bottom">
                <thead>
                    <tr class="w3-theme-l1">
                        <td><b>AÇÕES</b></td>
                        <td><b>ID</b></td>
                        <td><b>PACIENTE</b></td>
                        <td><b>PRODUTO</b></td>
                        <td><b>PREÇO</b></td>
                        <td><b>DATA VENDA</b></td>
                        <td><b>STATUS</b></td>
                        <td><b>DATA PAG.</b></td>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    while ($row = mysqli_fetch_assoc($result)) {
                        $id = $row['id'];
                        $paciente = $row['nome_completo'];
                        $produto = $row['nome'];
                        $preco = $row['valor_cobrado'];
                        $data_venda = $row['data_venda'];
                        $status = $row['status_pagamento'];
                        $data_pagamento = $row['data_pagamento'];
                    ?>
                        <tr>
                            <td>
                                <a href="venda_form.php?action=edit&id=<?= $id ?>" class="w3-text-blue w3-small">
                                    <span class="material-symbols-outlined">edit</span>
                                </a>
                                <a href="index.php?action=delete&id=<?= $id ?>" class="w3-text-red w3-small">
                                    <span class="material-symbols-outlined">delete</span>
                                </a>
                            </td>
                            <td><?= $id ?></td>
                            <td><?= $paciente ?></td>
                            <td><?= $produto ?></td>
                            <td><?= $preco ?></td>
                            <td><?= $data_venda ?></td>
                            <td><?= $status ?></td>
                            <td><?= $data_pagamento ?></td>
                        </tr>
                    <?php
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