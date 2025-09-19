<?php
$currentPage = 'psicologo';

include_once __DIR__ . '/../../includes/header.php';

$dados = array(
    'id' => '',
    'nome' => '',
    'crp' => '',
    'contato' => '',
    'chave_pix' => '',
    'agencia' => '',
    'conta' => '',
    'comissao' => 0.50,
    'data_inicio' => '',
    'ativo' => ''
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
            $sql = "INSERT INTO psicologo (
            nome_completo,
            crp,
            contato,
            chave_pix,
            agencia,
            conta,
            percentual_comissao,
            data_inicio,
            ativo)";
            $sql .= "VALUES (
            '{$dados['nome']}',
            '{$dados['crp']}',
            '{$dados['contato']}',
            '{$dados['chave_pix']}',
            '{$dados['agencia']}',
            '{$dados['conta']}',
            '{$dados['comissao']}',
            '{$dados['data_inicio']}',
            '{$dados['ativo']}')";

            $result = mysqli_query($conn, $sql);
        } else {
            $sql = "UPDATE psicologo SET";
            $sql .= " nome_completo = '{$dados['nome']}',";
            $sql .= " crp = '{$dados['crp']}',";
            $sql .= " contato = '{$dados['contato']}',";
            $sql .= " chave_pix = '{$dados['chave_pix']}',";
            $sql .= " agencia = '{$dados['agencia']}',";
            $sql .= " conta = '{$dados['conta']}',";
            $sql .= " percentual_comissao = '{$dados['comissao']}',";
            $sql .= " data_inicio = '{$dados['data_inicio']}',";
            $sql .= " ativo = '{$dados['ativo']}'";
            $sql .= " WHERE id = '{$dados['id']}'";

            $result = mysqli_query($conn, $sql);
        }

        print ($result) ? 'Registro salvo com sucesso.' : mysqli_error($conn);
        mysqli_close($conn);
        header('location: index.php');
    } elseif ($_REQUEST['action'] == 'edit') {
        $id = $_GET['id'];

        $sql = "SELECT * FROM psicologo WHERE id = '{$id}'";
        $result = mysqli_query($conn, $sql);

        if ($row = mysqli_fetch_assoc($result)) {
            $dados['id'] = $row['id'];
            $dados['nome'] = $row['nome_completo'];
            $dados['crp'] = $row['crp'];
            $dados['contato'] = $row['contato'];
            $dados['chave_pix'] = $row['chave_pix'];
            $dados['agencia'] = $row['agencia'];
            $dados['conta'] = $row['conta'];
            $dados['comissao'] = $row['percentual_comissao'];
            $dados['data_inicio'] = $row['data_inicio'];
            $dados['ativo'] = $row['ativo'];
        }
    }
}

?>

<div class="w3-container w3-padding  w3-margin-bottom">
    <div class="w3-container w3-padding  w3-margin-bottom">
        <h3>Formulário de Psicólogo</h3>
    </div>
    <div class="w3-card w3-white w3-padding w3-margin-top w3-round" style="max-width:750px; margin:auto;">
        <form enctype="multipart/form-data" method="post" action="psicologo_form.php?action=save" class="w3-container">

            <!-- Código -->
            <label>Código</label>
            <input name="id" readonly type="text" style="width: 25%" value="<?= $dados['id'] ?>" class="w3-input w3-border w3-sand w3-round">

            <!-- Nome Completo -->
            <label>Nome Completo</label>
            <input name="nome" type="text" value="<?= $dados['nome'] ?>" class="w3-input w3-border w3-round" required>

            <!-- CRP -->
            <label>Nº CRP</label>
            <input name="crp" type="text" value="<?= $dados['crp'] ?>" class="w3-input w3-border w3-round" required>

            <!-- Contato -->
            <label>Contato</label>
            <input name="contato" type="number" value="<?= $dados['contato'] ?>" class="w3-input w3-border w3-round" required>

            <!-- pix -->
            <label>Chave Pix</label>
            <input name="chave_pix" type="text" value="<?= $dados['chave_pix'] ?>" class="w3-input w3-border w3-round">

            <!-- Agência -->
            <label>Agência</label>
            <input name="agencia" type="number" value="<?= $dados['agencia'] ?>" class="w3-input w3-border w3-round">

            <!-- Conta -->
            <label>Nº Conta</label>
            <input name="conta" type="number" value="<?= $dados['conta'] ?>" class="w3-input w3-border w3-round">

            <!-- Conta -->
            <label>(%) Repasse</label>
            <input name="comissao" type="number" value="<?= number_format($dados['comissao'], 2) ?>" step="0.01" class="w3-input w3-border w3-round" required>

            <!-- Data de Inicio -->
            <label>Data de Inicio</label>
            <input name="data_inicio" type="date" value="<?= $dados['data_inicio'] ?>" class="w3-input w3-border w3-round" required>

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