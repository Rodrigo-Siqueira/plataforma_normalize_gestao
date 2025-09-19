<?php
$currentPage = 'produtos';

include_once __DIR__ . '/../../includes/header.php';

$conn = mysqli_connect(
    'localhost',
    'root',
    '',
    'dev_agendamento_db'
) or die('Erro de conexão');

# script para deletar
if (!empty($_GET['action']) and $_GET['action'] == 'delete') {

    $id = (int) $_GET['id'];
    $sql = "DELETE FROM produtos WHERE id = '{$id}'";
    $result = mysqli_query($conn, $sql);
}

$sql = "SELECT * FROM produtos";
$result = mysqli_query($conn, $sql);
?>

<div class="w3-container w3-padding  w3-margin-bottom">
    <h3>Gestão de Produtos</h3>
    <div class="w3-card w3-white w3-padding w3-margin-top w3-round-large">
        <div class="w3-row w3-margin-bottom  w3-margin-top">
            <div class="w3-col s12 m12 l12 w3-right-align">
                <a href="prod_form.php" class="w3-button w3-green w3-round">
                    <span class="material-symbols-outlined">add</span>
                    <b>Novo Produto</b>
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
                        <td><b>TIPO</b></td>
                        <td><b>SESSÃO</b></td>
                        <td><b>PREÇO</b></td>
                        <td><b>VALIDADE</b></td>
                        <td><b>ATIVO</b></td>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    while ($row = mysqli_fetch_assoc($result)) {
                        $id = $row['id'];
                        $nome = $row['nome'];
                        $tipo = $row['tipo'];
                        $sessoes = $row['numero_sessoes'];
                        $preco = $row['preco'];
                        $validade = $row['validade_dias'];
                        $ativo = $row['ativo'];
                    ?>
                        <tr>
                            <td>
                                <a href="prod_form.php?action=edit&id=<?= $id ?>" class="w3-text-blue w3-small">
                                    <i class="material-symbols-outlined">edit</i>
                                </a>
                                <a href="prod_lista.php?action=delete&id=<?= $id ?>" class="w3-text-red w3-small">
                                    <i class="material-symbols-outlined">delete</i>
                                </a>
                            </td>
                            <td><?= $id ?></td>
                            <td><?= $nome ?></td>
                            <td><?= $tipo ?></td>
                            <td><?= $sessoes ?></td>
                            <td><?= $preco ?></td>
                            <td><?= $validade . ' Dias' ?></td>
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