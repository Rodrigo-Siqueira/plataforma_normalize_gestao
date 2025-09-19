<?php
$currentPage = 'repasse';

include_once __DIR__ . '/../../includes/header.php';

$conn = mysqli_connect(
    'localhost',
    'root',
    '',
    'dev_agendamento_db'
) or die('Erro de conexão');

$sql = "
    SELECT 
	l.id,
	p.nome_completo,
    l.periodo_inicio,
    l.periodo_fim,
    l.valor_liquido_pago,
    l.status
FROM lotes_repasse AS l 
JOIN psicologo p ON p.id = l.psicologo_id
ORDER BY l.id";

$result  = mysqli_query($conn, $sql);

?>

<div class="w3-container w3-padding  w3-margin-bottom">
    <h3>Gestão de Repasses</h3>
    <div class="w3-card w3-white w3-padding w3-margin-top w3-round-large">
        <div class="w3-row w3-margin-bottom  w3-margin-top">
            <div class="w3-col s12 m12 l12 w3-right-align">
                <a href="repasse_form.php" class="w3-button w3-green w3-round">
                    <span class="material-symbols-outlined">add</span>
                    <b>Novo Repasse</b>
                </a>
            </div>
        </div>
        <div class="w3-responsive ">
            <table class="w3-table-all w3-hoverable w3-striped w3-margin-bottom">
                <thead>
                    <tr class="w3-theme-l1">
                        <td><b>AÇÕES</b></td>
                        <td><b>ID</b></td>
                        <td><b>PSICÓLOGO</b></td>
                        <td><b>DATA INICIAL</b></td>
                        <td><b>DATA FINAL</b></td>
                        <td><b>VALOR PAGO</b></td>
                        <td><b>STATUS</b></td>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    while ($row = mysqli_fetch_assoc($result)) {
                        $id = $row['id'];
                        $psico = $row['nome_completo'];
                        $data_inicio = date('d-m-Y', strtotime($row['periodo_inicio']));
                        $data_fim = date('d-m-Y', strtotime($row['periodo_fim']));
                        $preco = $row['valor_liquido_pago'];
                        $status = $row['status'];
                    ?>
                        <tr>
                            <td>
                                <a href="update_repasse_form.php?action=edit&id=<?= $id ?>" class="w3-text-blue w3-small">
                                    <span class="material-symbols-outlined">edit</span>
                                </a>
                                <a href="index.php?action=delete&id=<?= $id ?>" class="w3-text-red w3-small">
                                    <span class="material-symbols-outlined">delete</span>
                                </a>
                            </td>
                            <td><?= $id ?></td>
                            <td><?= $psico ?></td>
                            <td><?= $data_inicio ?></td>
                            <td><?= $data_fim ?></td>
                            <td><?= $preco ?></td>
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