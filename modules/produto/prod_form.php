<?php
$currentPage = 'produtos';

include_once __DIR__ . '/../../includes/header.php';

$dados = array(
    "id" => '',
    "nome" => '',
    "tipo" => '',
    "sessoes" => '',
    "preco" => '',
    "validade" => 90,
    "ativo" => '',
);

if (!empty($_REQUEST['action'])) {
    $conn = mysqli_connect(
        'localhost',
        'root',
        '',
        'dev_agendamento_db'
    ) or die('Erro de conexão');

    if ($_REQUEST['action'] == 'save') {
        $dados = $_POST;

        if (empty($dados['id'])) {
            $sql = "INSERT INTO produtos (
            nome, 
            tipo, 
            numero_sessoes, 
            preco, 
            validade_dias, 
            ativo)";
            $sql .= "VALUES (
            '{$dados['nome']}',
            '{$dados['tipo']}',
            '{$dados['sessoes']}',
            '{$dados['preco']}',
            '{$dados['validade']}',
            '{$dados['ativo']}')";

            $result = mysqli_query($conn, $sql);
        } else {
            $sql = "UPDATE produtos SET";
            $sql .= " nome = '{$dados['nome']}',";
            $sql .= " tipo = '{$dados['tipo']}',";
            $sql .= " numero_sessoes = '{$dados['sessoes']}',";
            $sql .= " preco = '{$dados['preco']}',";
            $sql .= " validade_dias = '{$dados['validade']}',";
            $sql .= " ativo = '{$dados['ativo']}'";
            $sql .= " WHERE id = '{$dados['id']}'";

            $result = mysqli_query($conn, $sql);
        }

        print ($result) ? 'Registro salvo com sucesso.' : mysqli_error($conn);
        mysqli_close($conn);

        header('location: index.php');
    } elseif ($_REQUEST['action'] == 'edit') {
        $id = $_GET['id'];

        $sql = "SELECT * FROM produtos WHERE id='{$id}'";
        $result = mysqli_query($conn, $sql);
        if ($row = mysqli_fetch_assoc($result)) {
            $dados['id'] = $row['id'];
            $dados['nome'] = $row['nome'];
            $dados['tipo'] = $row['tipo'];
            $dados['sessoes'] = $row['numero_sessoes'];
            $dados['preco'] = $row['preco'];
            $dados['validade'] = $row['validade_dias'];
            $dados['ativo'] = $row['ativo'];
        }
    }
}
?>

<div class="w3-container w3-padding  w3-margin-bottom">
    <div class="w3-container w3-padding  w3-margin-bottom">
        <h3>Formulário de Produto</h3>
    </div>

    <div class="w3-card w3-white w3-padding w3-margin-top w3-round" style="max-width:750px; margin:auto;">
        <form enctype="multipart/form-data" method="post" action="prod_form.php?action=save" class="w3-container">

            <!-- Código -->
            <label>Código</label>
            <input name="id" readonly type="text" style="width: 25%" value="<?= $dados['id'] ?>" class="w3-input w3-border w3-sand w3-round">

            <!-- Nome -->
            <label>Nome</label>
            <input name="nome" type="text" value="<?= $dados['nome'] ?>" class="w3-input w3-border w3-round" required>

            <!-- Tipo -->
            <label>Tipo</label>
            <select name="tipo" class="w3-select w3-border w3-round" required>
                <option value="">Selecione...</option>
                <option value="PACOTE" <?= ($dados['tipo'] == 'PACOTE') ? 'selected' : '' ?>>Pacote</option>
                <option value="AVULSO" <?= ($dados['tipo'] == 'AVULSO') ? 'selected' : '' ?>>Avulso</option>
            </select>

            <!-- Quantidade de Sessões -->
            <label>Quantidade de Sessões</label>
            <input name="sessoes" type="number" value="<?= $dados['sessoes'] ?>" class="w3-input w3-border w3-round" required>

            <!-- Preço -->
            <label>Preço</label>
            <input name="preco" type="number" value="<?= $dados['preco'] ?>" class="w3-input w3-border w3-round" required>

            <!-- Validade -->
            <label>Validade</label>
            <input name="validade" type="number" value="<?= $dados['validade'] ?>" class="w3-input w3-border w3-round" required>

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
include_once __DIR__ . '/../../includes/footer.php';
?>