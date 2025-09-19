<?php
$currentPage = 'despesas';

include_once __DIR__ . '/../../includes/header.php';

$conn = mysqli_connect(
    'localhost',
    'root',
    '',
    'dev_agendamento_db'
) or die('Erro de conexão');

$sql = "SELECT * FROM despesas_operacionais";
$result = mysqli_query($conn, $sql);
?>

<div class="w3-container w3-padding  w3-margin-bottom">
    <h3>Gestão de Despesas</h3>
    <div class="w3-card w3-white w3-padding w3-margin-top w3-round-large">
        <div class="w3-row w3-margin-bottom  w3-margin-top">
            <div class="w3-col s12 m12 l12 w3-right-align">
                <a href="despesa_form.php" class="w3-button w3-green w3-round">
                    <span class="material-symbols-outlined">add</span>
                    <b>Nova Despesa</b>
                </a>
            </div>
        </div>
        <div class="w3-responsive ">
            <table class="w3-table-all w3-hoverable w3-striped w3-margin-bottom">
                <thead>
                    <tr class="w3-theme-l1">
                        <td><b>AÇÕES</b></td>
                        <td><b>ID</b></td>
                        <td><b>DESCRIÇÃO</b></td>
                        <td><b>TIPO</b></td>
                        <td><b>CATEGORIA</b></td>
                        <td><b>VALOR</b></td>
                        <td><b>DATA DA DESP.</b></td>
                        <td><b>DATA DO PGTO</b></td>
                        <td><b>STATUS</b></td>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    while ($row = mysqli_fetch_assoc($result)) {
                        $id = $row['id'];
                        $descricao = $row['descricao'];
                        $tipo = $row['tipo'];
                        $categoria = $row['categoria'];
                        $valor = $row['valor'];
                        $data_competencia = $row['data_competencia'];
                        $data_pagamento = $row['data_pagamento'];
                        $status = $row['status'];
                    ?>
                        <tr>
                            <td>
                                <a href="despesa_form.php?action=edit&id=<?= $id ?>" class="w3-text-blue w3-small">
                                    <span class="material-symbols-outlined">edit</span>
                                </a>
                                <a href="lista.php?action=delete&id=<?= $id ?>" class=" w3-text-red w3-small">
                                    <span class="material-symbols-outlined">delete</span>
                                </a>
                            </td>
                            <td><?= $id ?></td>
                            <td><?= $descricao ?></td>
                            <td><?= $tipo ?></td>
                            <td><?= $categoria ?></td>
                            <td><?= $valor ?></td>
                            <td><?= $data_competencia ?></td>
                            <td><?= $data_pagamento ?></td>
                            <td><?= $status ?></td>
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