<?php
$currentPage = 'resp_financeiro';
include_once __DIR__ . '/../../includes/header.php';

$conn = mysqli_connect(
    'localhost',
    'root',
    '',
    'dev_agendamento_db'
) or die('Erro de conexão');

if (!empty($_GET['action']) and $_GET['action'] == 'delete') {
    $id = $_GET['id'];

    $sql = "DELETE FROM responsaveis_financeiros WHERE id = '{$id}'";
    mysqli_query($conn, $sql);
}

$sql = "SELECT * FROM responsaveis_financeiros";
$result = mysqli_query($conn, $sql);
?>

<div class="w3-container w3-padding  w3-margin-bottom">
    <h3>Gestão de Responsável Financeiro</h3>
    <div class="w3-card w3-white w3-padding w3-margin-top w3-round-large">
        <div class="w3-row w3-margin-bottom  w3-margin-top">
            <div class="w3-col s12 m12 l12 w3-right-align">
                <a href="resp_financeiro_form.php" class="w3-button w3-green w3-round">
                    <span class="material-symbols-outlined">add</span>
                    <b>Novo Resp. Financeiro</b>
                </a>
            </div>
        </div>
        <div class="w3-responsive ">
            <table class="w3-table-all w3-hoverable w3-striped w3-margin-bottom">
                <thead>
                    <tr class="w3-theme-l1">
                        <td><b>AÇÕES</b></td>
                        <td><b>ID</b></td>
                        <td><b>NOME</b></td>
                        <td><b>CONTATO</b></td>
                        <td><b>PACIENTE</b></td>
                        <td><b>ATIVO</b></td>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    while ($row = mysqli_fetch_assoc($result)) {
                        $id = $row['id'];
                        $nome = $row['nome_completo'];
                        $contato = $row['contato'];
                        $paciente = $row['paciente'];
                        $ativo = $row['ativo'];
                    ?>
                        <tr>
                            <td>
                                <a href="resp_financeiro_form.php?action=edit&id=<?= $id ?>" class="w3-text-blue w3-small">
                                    <span class="material-symbols-outlined">edit</span>
                                </a>
                                <a href="index.php?action=delete&id=<?= $id ?>" class="w3-text-red w3-small">
                                    <span class="material-symbols-outlined">delete</span>
                                </a>
                            </td>
                            <td><?= $id ?></td>
                            <td><?= $nome ?></td>
                            <td><?= $contato ?></td>
                            <td><?= ($paciente == 1) ? 'Sim' : 'Não' ?></td>
                            <td><?= ($ativo == 1) ? 'Sim' : 'Não' ?></td>

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