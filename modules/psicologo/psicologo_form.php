<?php
require_once 'regras_psicologo.php';

$dados = array(
    'id' => '',
    'nome' => '',
    'crp' => '',
    'contato' => '',
    'comissao' => 0.50,
    'data_inicio' => '',
    'ativo' => 1
);

if (!empty($_REQUEST['action'])) {

    if ($_REQUEST['action'] == 'save') {
        $dados = $_POST;

        if (empty($dados['id'])) {
            $resultado = adicionar_psicologo($dados);

        } else {
            $resultado = alterar_psicologo($dados);
        }

        if ($resultado['sucesso']) {
            print "<script>
                    alert('" . addslashes($resultado['mensagem']) . "');
                    window.location.href = 'index.php';
                </script>";
        } else {
            print "<script>
                    alert('" . addslashes($resultado['erro']) . "');
                    window.location.href = 'index.php';
                </script>";
        }
    } elseif ($_REQUEST['action'] == 'edit') {
        $id = (int) $_GET['id'];

        $resultado = buscar_psicologo_id($id);

        if ($resultado) {
            $dados['id'] = $resultado['id'];
            $dados['nome'] = $resultado['nome_completo'];
            $dados['crp'] = $resultado['crp'];
            $dados['contato'] = $resultado['contato'];
            $dados['comissao'] = $resultado['percentual_comissao'];
            $dados['data_inicio'] = $resultado['data_inicio'];
            $dados['ativo'] = $resultado['ativo'];
        }
    }
}

$currentPage = 'psicologo';
include_once __DIR__ . '/../../includes/header.php';
?>

<div class="w3-container w3-padding  w3-margin-bottom">
    <div class="w3-container w3-padding  w3-margin-bottom">
        <h3>Cadastro de Psicólogo</h3>
    </div>
    <div class="w3-card w3-white w3-padding w3-margin-top w3-round" style="max-width:650px; margin:auto;">
        <form enctype="multipart/form-data" method="post" action="psicologo_form.php?action=save" class="w3-container">

            <div class="w3-row-padding w3-margin-top">
                <div class="w3-third">
                    <!-- Código -->
                    <label>Código</label>
                    <input name="id" readonly type="text" value="<?= $dados['id'] ?>" class="w3-input w3-border w3-sand w3-round">
                </div>

                <div class="w3-third w3-right">
                    <!-- Ativo -->
                    <label>Ativo</label>
                    <select name="ativo" class="w3-select w3-border w3-round" required>
                        <option value="">Selecione...</option>
                        <option value="1" <?= ($dados['ativo'] == '1') ? 'selected' : '' ?>>Sim</option>
                        <option value="0" <?= ($dados['ativo'] == '0') ? 'selected' : '' ?>>Não</option>
                    </select>
                </div>
            </div>

            <div class="w3-row-padding w3-margin-top">
                <div class="w3-col">
                    <!-- Nome Completo -->
                    <label>Nome Completo</label>
                    <input name="nome" type="text" value="<?= $dados['nome'] ?>" class="w3-input w3-border w3-round" required>
                </div>
            </div>

            <div class="w3-row-padding w3-margin-top">
                <!-- Código -->
                <div class="w3-half">
                    <!-- CRP -->
                    <label>Nº CRP</label>
                    <input name="crp" type="text" value="<?= $dados['crp'] ?>" class="w3-input w3-border w3-round" required>
                </div>

                <div class="w3-half">
                    <!-- Contato -->
                    <label>Contato</label>
                    <input name="contato" type="number" value="<?= $dados['contato'] ?>" class="w3-input w3-border w3-round" required>
                </div>
            </div>

            <div class="w3-row-padding w3-margin-top">
                <div class="w3-half">
                    <!-- Repasse -->
                    <label>(%) Repasse</label>
                    <input name="comissao" type="number" value="<?= number_format($dados['comissao'], 2) ?>" step="0.01" class="w3-input w3-border w3-round" required>
                </div>

                <div class="w3-half">
                    <!-- Data de Inicio -->
                    <label>Data de Inicio</label>
                    <input name="data_inicio" type="date" value="<?= $dados['data_inicio'] ?>" class="w3-input w3-border w3-round" required>
                </div>
            </div>

            <div class="w3-row-padding w3-margin-top">
                <div class="w3-col">
                    <!-- Botões -->
                    <button type="submit" class="w3-button w3-green w3-round w3-margin-bottom w3-margin-right">
                        <span class="material-symbols-outlined">save</span>
                        <b>Salvar</b>
                    </button>
                    <button type="reset" class="w3-button w3-red w3-round w3-margin-bottom w3-margin-right">
                        <span class="material-symbols-outlined">cancel</span>
                        <b>Cancelar</b>
                    </button>
                </div>
            </div>

        </form>
    </div>
</div>

<?php
include_once __DIR__ . '/../../includes/footer.php';
?>