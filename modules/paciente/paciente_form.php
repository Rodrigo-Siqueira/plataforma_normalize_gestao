<?php
$currentPage = 'paciente';

include_once __DIR__ . '/../../includes/header.php';

# Inicializa a variavel $dados vazios.
$dados = array(
    'id' => '',
    'responsavel_id' => '',
    'nome' => '',
    'contato' => '',
    'ativo' => ''
);

# Inicia a conexão com banco
$conn = mysqli_connect(
    'localhost',
    'root',
    '',
    'dev_agendamento_db'
) or die('Erro de conexão');

if (!empty($_REQUEST['action'])) {
    if ($_REQUEST['action'] == 'save') {
        $dados = $_POST;

        if (empty($dados['id'])) {
            $sql = "INSERT INTO pacientes (
            responsavel_financeiro_id,
            nome_completo,
            contato,
            ativo)";
            $sql .= "VALUES (
            '{$dados['responsavel_id']}',
            '{$dados['nome']}',
            '{$dados['contato']}',
            '{$dados['ativo']}')";

            $result = mysqli_query($conn, $sql);
        } else {
            $sql = "UPDATE pacientes SET";
            $sql .= " responsavel_financeiro_id = '{$dados['responsavel_id']}',";
            $sql .= " nome_completo = '{$dados['nome']}',";
            $sql .= " contato = '{$dados['contato']}',";
            $sql .= " ativo = '{$dados['ativo']}'";
            $sql .= " WHERE id = '{$dados['id']}'";

            $result = mysqli_query($conn, $sql);
        }

        print ($result) ? 'Registro salvo com sucesso.' : mysqli_error($conn);
        header('location: index.php');
    } elseif ($_REQUEST['action'] == 'edit') {
        $id = $_GET['id'];

        $sql = "SELECT * FROM pacientes WHERE id = '{$id}'";
        $result = mysqli_query($conn, $sql);

        if ($row = mysqli_fetch_assoc($result)) {
            $dados['id'] = $row['id'];
            $dados['responsavel_id'] = $row['responsavel_financeiro_id'];
            $dados['nome'] = $row['nome_completo'];
            $dados['contato'] = $row['contato'];
            $dados['ativo'] = $row['ativo'];
        }
    }
}

# Lista todos os Responsáveis financeiros
$sql = "SELECT id, nome_completo FROM responsaveis_financeiros WHERE ativo = '1'";
$result = mysqli_query($conn, $sql);

?>

<div class="w3-container w3-padding  w3-margin-bottom">
    <div class="w3-container w3-padding  w3-margin-bottom">
        <h3>Formulário de Pacientes</h3>
    </div>
    <div class="w3-card w3-white w3-padding w3-margin-top w3-round" style="max-width:750px; margin:auto;">
        <form enctype="multipart/form-data" method="post" action="paciente_form.php?action=save" class="w3-container">

            <!-- Código -->
            <label>Código</label>
            <input name="id" readonly type="text" style="width: 25%" value="<?= $dados['id'] ?>" class="w3-input w3-border w3-sand w3-round">

            <!-- Nome Completo -->
            <label>Nome Completo</label>
            <input name="nome" type="text" value="<?= $dados['nome'] ?>" class="w3-input w3-border w3-round" required>

            <!-- Contato -->
            <label>Contato</label>
            <input name="contato" type="number" value="<?= $dados['contato'] ?>" class="w3-input w3-border w3-round" required>

            <!-- Responsável Financeiro -->
            <label>Responsável Financeiro</label>
            <select name="responsavel_id" class="w3-select w3-border w3-round" required>
                <option value="">Selecione...</option>

                <?php
                if ($result) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        $selected = ($row['id'] == $dados['responsavel_id']) ? 'selected' : '';
                        print "<option value='{$row['id']}' $selected>{$row['nome_completo']}</option>";
                    }
                }
                ?>

            </select>

            <!-- Ativo -->
            <label>Ativo</label>
            <select name="ativo" class="w3-select w3-border w3-margin-bottom w3-round" required>
                <option value="">Selecione...</option>
                <option value="1" <?= ($dados['ativo'] == '1') ? 'selected' : '' ?>>Sim</option>
                <option value="0" <?= ($dados['ativo'] == '0') ? 'selected' : '' ?>>Não</option>
            </select>

            <!-- Botões -->
            <button type="submit" class="w3-button w3-green w3-round w3-margin-bottom w3-margin-right">
                <span class="material-symbols-outlined">save</span>
                <b>Salvar</b>
            </button>
            <button type="reset" class="w3-button w3-red w3-round w3-margin-bottom w3-margin-right">
                <span class="material-symbols-outlined">cancel</span>
                <b>Cancelar</b>
            </button>
        </form>
    </div>
</div>


<?php
mysqli_close($conn);
include_once __DIR__ . '/../../includes/footer.php';
?>