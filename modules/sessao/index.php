<?php
$currentPage = 'sessao';

include_once __DIR__ . '/../../includes/header.php';

$conn = mysqli_connect(
    'localhost',
    'root',
    '',
    'dev_agendamento_db'
) or die('Erro de conexão');

$sql = "
    SELECT *
    FROM (
        SELECT 
            s.id, 
            p.nome_completo AS paciente,
            psi.nome_completo AS psicologo,
            s.data_agendada, 
            s.hora_agendada, 
            s.status,
            CONCAT(
                ROW_NUMBER() OVER (
                    PARTITION BY s.venda_origem_id 
                    ORDER BY s.data_agendada
                ),
                '/',
                prod.numero_sessoes
            ) AS carimbo
        FROM sessoes AS s
        JOIN pacientes AS p ON p.id = s.paciente_id
        JOIN psicologo AS psi ON psi.id = s.psicologo_id
        JOIN vendas v ON v.id = s.venda_origem_id
        JOIN produtos prod ON prod.id = v.produto_id
    ) AS todas
    WHERE todas.status = 'AGENDADA'
    ORDER BY todas.data_agendada
";

$result  = mysqli_query($conn, $sql);

?>

<div class="w3-container w3-padding  w3-margin-bottom">
    <h3>Gestão de Sessões</h3>
    <div class="w3-card w3-white w3-padding w3-margin-top w3-round-large">
        <div class="w3-row w3-margin-bottom  w3-margin-top">
            <div class="w3-col s12 m12 l12 w3-right-align">
                <a href="sessao_form.php" class="w3-button w3-green w3-round">
                    <span class="material-symbols-outlined">add</span>
                    <b>Nova Sessão</b>
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
                        <td><b>PSICÓLOGO</b></td>
                        <td><b>CARIMBO</b></td>
                        <td><b>DATA</b></td>
                        <td><b>HORA</b></td>
                        <td><b>STATUS</b></td>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    while ($row = mysqli_fetch_assoc($result)) {
                        $id = $row['id'];
                        $paciente = $row['paciente'];
                        $psico = $row['psicologo'];
                        $carimbo = $row['carimbo'];
                        $data = date('d-m-Y', strtotime($row['data_agendada']));
                        $hora = $row['hora_agendada'];
                        $status = $row['status'];
                    ?>
                        <tr>
                            <td>
                                <a href="sessao_form.php?action=edit&id=<?= $id ?>" class="w3-text-blue w3-small">
                                    <span class="material-symbols-outlined">edit</span>
                                </a>
                            </td>
                            <td><?= $id ?></td>
                            <td><?= $paciente ?></td>
                            <td><?= $psico ?></td>
                            <td><?= $carimbo ?></td>
                            <td><?= $data ?></td>
                            <td><?= $hora ?></td>
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