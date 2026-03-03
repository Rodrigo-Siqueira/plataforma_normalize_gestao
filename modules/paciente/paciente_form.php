<?php
require_once __DIR__ . '/../../config/config.php';
require_once 'regras_paciente.php';
require_once '../resp_financeiro/regras_resp_financeiro.php';
require_once '../psicologo/regras_psicologo.php';

$responsavel_financ = listar_resp_financeiros();
$psicologos = listar_psicologo();

# Inicializa a variavel $dados vazios.
$dados = array(
    'id' => '',
    'psicologo_id' => '',
    'responsavel_id' => '',
    'nome' => '',
    'contato' => '',
    'ativo' => 1
);

if (!empty($_REQUEST['action'])) {
    if ($_REQUEST['action'] == 'save') {
        $dados = $_POST;

        if (empty($dados['id'])) {

            $resultado = adicionar_paciente($dados);
        } else {

            $resultado = alterar_paciente($dados);
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

        $resultado = buscar_paciente_id($id);

        if ($resultado) {
            $dados['id'] = $resultado['id'];
            $dados['responsavel_id'] = $resultado['responsavel_financeiro_id'];
            $dados['psicologo_id'] = $resultado['psicologo_responsavel_id'];
            $dados['nome'] = $resultado['nome_completo'];
            $dados['contato'] = $resultado['contato'];
            $dados['ativo'] = $resultado['ativo'];
        }
    }
}

$currentPage = 'paciente';
include_once __DIR__ . '/../../includes/header.php';
?>

<div class="w3-container w3-padding  w3-margin-bottom">
    <div class="w3-container w3-padding  w3-margin-bottom">
        <h3>Cadastro de Pacientes</h3>
    </div>
    <div class="w3-card w3-white w3-padding w3-margin-top w3-round" style="max-width:750px; margin:auto;">
        <form enctype="multipart/form-data" method="post" action="paciente_form.php?action=save" class="w3-container">

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
                    <!-- Responsável Financeiro -->
                    <label>Responsável Financeiro</label>
                    <select name="responsavel_id" class="w3-select w3-border w3-round" required>
                        <option value="">Selecione...</option>
                        <?php
                            if ($responsavel_financ) {
                                foreach ($responsavel_financ as $responsavel) {
                                    $selected = ($responsavel['id'] == $dados['responsavel_id']) ? 'selected' : '';
                                    print "<option value='{$responsavel['id']}' $selected>{$responsavel['nome_completo']}</option>";
                                }
                            }
                        ?>
                    </select>
                </div>
            </div>

            <div class="w3-row-padding w3-margin-top">
                <div class="w3-col">
                    <!-- Psicologo -->
                    <label>Psicologo</label>
                    <select name="psicologo_id" class="w3-select w3-border w3-round" required>
                        <option value="">Selecione...</option>
                        <?php
                            if ($psicologos) {
                                foreach ($psicologos as $psicologo) {
                                    $selected = ($psicologo['id'] == $dados['psicologo_id']) ? 'selected' : '';
                                    print "<option value='{$psicologo['id']}' $selected>{$psicologo['nome_completo']}</option>";
                                }
                            }  
                        ?>
                    </select>
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