<?php
$currentPage = 'paciente';

include_once __DIR__ . '/../../includes/header.php';

$conn = mysqli_connect(
    'localhost',
    'root',
    '',
    'dev_agendamento_db'
) or die('Erro de conexão');

$sql = "
    SELECT 
        p.id, 
        p.nome_completo AS paciente_nome, 
        p.contato, 
        r.nome_completo AS responsavel_nome,
        p.ativo
    FROM pacientes AS p
    INNER JOIN responsaveis_financeiros AS r 
        ON p.responsavel_financeiro_id = r.id
";

$result = mysqli_query($conn, $sql);

?>

<div class="w3-container w3-padding  w3-margin-bottom">
    <h3>Gestão de Pacientes</h3>
    <div class="w3-card w3-white w3-padding w3-margin-top w3-round-large">
        <div class="w3-row w3-margin-bottom  w3-margin-top">
            <div class="w3-col s12 m12 l12 w3-right-align">
                <a href="paciente_form.php" class="w3-button w3-green w3-round">
                    <span class="material-symbols-outlined">add</span>
                    <b>Novo Paciente</b>
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
                        <td><b>RESP. FIN.</b></td>
                        <td><b>ATIVO</b></td>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    while ($row = mysqli_fetch_assoc($result)) {
                        $id = $row['id'];
                        $nome = $row['paciente_nome'];
                        $contato = $row['contato'];
                        $responsavel = $row['responsavel_nome'];
                        $ativo = $row['ativo'];
                    ?>
                        <tr>
                            <td>
                                <a href="paciente_form.php?action=edit&id=<?= $id ?>" class="w3-text-blue w3-small">
                                    <span class="material-symbols-outlined">edit</span>
                                </a>
                                <a href="index.php?action=delete&id=<?= $id ?>" class="w3-text-red w3-small">
                                    <span class="material-symbols-outlined">delete</span>
                                </a>
                            </td>
                            <td><?= $id ?></td>
                            <td><?= $nome ?></td>
                            <td><?= $contato ?></td>
                            <td><?= $responsavel ?></td>
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