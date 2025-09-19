<?php
$currentPage = 'psicologo';

include_once __DIR__ . '/../../includes/header.php';

$conn = mysqli_connect(
    'localhost',
    'root',
    '',
    'dev_agendamento_db'
) or die('Erro de conexão');

$sql = "SELECT * FROM psicologo";
$result = mysqli_query($conn, $sql);

if (!empty($_GET['action']) and $_GET['action'] == 'delete') {
    $id = $_GET['id'];

    $sql = "DELETE FROM psicologo WHERE id = '{$id}'";
    mysqli_query($conn, $sql);
}

?>

<div class="w3-container w3-padding  w3-margin-bottom">
    <h3>Gestão de Psicólogo</h3>
    <div class="w3-card w3-white w3-padding w3-margin-top w3-round-large">
        <div class="w3-row w3-margin-bottom  w3-margin-top">
            <div class="w3-col s12 m12 l12 w3-right-align">
                <a href="psicologo_form.php" class="w3-button w3-green w3-round">
                    <span class="material-symbols-outlined">add</span>
                    <b>Novo Psicólogo</b>
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
                        <td><b>CRP</b></td>
                        <td><b>CONTATO</b></td>
                        <td><b>CHAVE PIX</b></td>
                        <td><b>% REP.</b></td>
                        <td><b>DATA INICIO</b></td>
                        <td><b>ATIVO</b></td>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    while ($row = mysqli_fetch_assoc($result)) {
                        $id = $row['id'];
                        $nome = $row['nome_completo'];
                        $crp = $row['crp'];
                        $contato = $row['contato'];
                        $pix = $row['chave_pix'];
                        $repasse = $row['percentual_comissao'];
                        $data = $row['data_inicio'];
                        $ativo = $row['ativo'];
                    ?>
                        <tr>
                            <td>
                                <a href="psicologo_form.php?action=edit&id=<?= $id ?>" class="w3-text-blue w3-small">
                                    <span class="material-symbols-outlined">edit</span>
                                </a>
                                <a href="index.php?action=delete&id=<?= $id ?>" class="w3-text-red w3-small">
                                    <span class="material-symbols-outlined">delete</span>
                                </a>
                            </td>
                            <td><?= $id ?></td>
                            <td><?= $nome ?></td>
                            <td><?= $crp ?></td>
                            <td><?= $contato ?></td>
                            <td><?= $pix ?></td>
                            <td><?= $repasse ?></td>
                            <td><?= $data ?></td>
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