<?php
require_once __DIR__ . '/../../config/config.php';
require_once 'regras_resp_financeiro.php';

$dados = array(
    'id' => '',
    'nome' => '',
    'contato' => '',
    'eh_paciente' => 1
);

if (!empty($_REQUEST['action'])) {

    if ($_REQUEST['action'] == 'save') {
        $dados = $_POST;

        if (empty($dados['id'])) {
            $resultado = adicionar_resp_financeiro($dados);

        } else {
            $resultado = alterar_resp_financeiro($dados);
            
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

        $resultado = buscar_resp_financeiro_id($id);

        if ($resultado) {
            $dados['id'] = $resultado['id'];
            $dados['nome'] = $resultado['nome_completo'];
            $dados['contato'] = $resultado['contato'];
            $dados['eh_paciente'] = $resultado['eh_paciente'];
        }
    }
}

$currentPage = 'resp_financeiro';
include_once __DIR__ . '/../../includes/header.php';
?>
<div class="w3-container w3-padding  w3-margin-bottom">
    <div class="w3-container w3-padding  w3-margin-bottom">
        <h3>Cadastro de Responsável Financeiro</h3>
    </div>
    <div class="w3-card w3-white w3-padding w3-margin-top w3-round" style="max-width:750px; margin:auto;">
        <form enctype="multipart/form-data" method="post" action="resp_financeiro_form.php?action=save" class="w3-container">

            <div class="w3-row-padding w3-margin-top">
                <div class="w3-third">
                    <!-- Código -->
                    <label>Código</label>
                    <input name="id" readonly type="text" value="<?= $dados['id'] ?>" class="w3-input w3-border w3-sand w3-round">
                </div>
                <div class="w3-third w3-right">
                    <!-- Paciente -->
                    <label>Paciente</label>
                    <select name="eh_paciente" class="w3-select w3-border w3-round" required>
                        <option value="">Selecione...</option>
                        <option value="1" <?= ($dados['eh_paciente'] == '1') ? 'selected' : '' ?>>Sim</option>
                        <option value="0" <?= ($dados['eh_paciente'] == '0') ? 'selected' : '' ?>>Não</option>
                    </select>
                </div>
            </div>

            <div class="w3-row-padding w3-margin-top">
                <div class="w3-threequarter">
                    <!-- Nome Completo -->
                    <label>Nome Completo</label>
                    <input name="nome" type="text" value="<?= $dados['nome'] ?>" class="w3-input w3-border w3-round" required>
                </div>
                <div class="w3-quarter">
                    <!-- Contato -->
                    <label>Contato</label>
                    <input name="contato" type="number" value="<?= $dados['contato'] ?>" class="w3-input w3-border w3-round" required>
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