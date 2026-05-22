<?php

require_once __DIR__ . '/../../config/config.php';
require_once 'regras_repasse.php';
require_once '../sessao/regras_sessao.php';
require_once '../psicologo/regras_psicologo.php';

$psicologos = listar_psicologo();

$dados = array(
    'id' => '',
    'psicologo_id' => '',
    'periodo_inicio' => '',
    'periodo_fim' => '',
    'valor_liquido_pago' => '',
    'status' => ''
);

if (!empty($_REQUEST['action'])) {
    if ($_REQUEST['action'] == 'save') {
        $dados = $_POST;

        if (empty($dados['id'])) {
            print 'O lote não foi carregado.';
        } else {
            $resultado = alterar_repasse($dados);

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
        }
    } elseif ($_REQUEST['action'] == 'edit') {
        $id = (int)$_GET['id'];
        $resultado = buscar_repasse_por_id($id);

        if ($resultado) {
            $dados['id'] = $resultado['id'];
            $dados['psicologo_id'] = $resultado['psicologo_id'];
            $dados['periodo_inicio'] = $resultado['periodo_inicio'];
            $dados['periodo_fim'] = $resultado['periodo_fim'];
            $dados['valor_liquido_pago'] = $resultado['valor_liquido_pago'];
            $dados['status'] = $resultado['status'];
        }

        $sessoes_vinculadas = listar_sessoes_vinculada_repasse($id);
    }
}

$currentPage = 'repasse';
include_once __DIR__ . '/../../includes/header.php';
?>

<div class="w3-container w3-padding  w3-margin-bottom">
    <div class="w3-container w3-padding  w3-margin-bottom">
        <h3>Atualizar Repasse</h3>
    </div>
    <div class="w3-card w3-white w3-padding w3-margin-top w3-round" style="max-width:750px; margin:auto;">
        <form enctype="multipart/form-data" method="post" action="update_repasse_form.php?action=save">

            <!-- Código -->
            <div class="w3-row-padding w3-margin-top">
                <div class="w3-quarter">
                    <label>Código</label>
                    <input name="id" readonly type="text" value="<?= $dados['id'] ?>" class="w3-input w3-border w3-sand w3-round">
                </div>

                <!-- Status -->
                <div class="w3-threequarter">
                    <label>Status</label>
                    <select name="status" class="w3-select w3-border w3-margin-bottom w3-round" required>
                        <option value="">Selecione...</option>
                        <option value="ABERTO" <?= ($dados['status'] == 'ABERTO') ? 'selected' : '' ?>>Aberto</option>
                        <option value="PAGO" <?= ($dados['status'] == 'PAGO') ? 'selected' : '' ?>>Pago</option>
                    </select>
                </div>
            </div>

            <!-- Psicólogo -->
            <div class="w3-row-padding w3-margin-top">
                <div class="w3-threequarter">
                    <label>Psicólogo</label>
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
                <div class="w3-quarter">
                    <label>Valor Total</label>
                    <input name="valor_liquido_pago" type="number" value="<?= $dados['valor_liquido_pago'] ?>" class="w3-input w3-border w3-round" required>
                </div>
            </div>

            <!-- Data Inicio -->
            <div class="w3-row-padding w3-margin-top">
                <div class="w3-half">
                    <label>Data Inicial</label>
                    <input name="periodo_inicio" type="date" value="<?= $dados['periodo_inicio'] ?>" class="w3-input w3-border w3-round" required>
                </div>
                <div class="w3-half">
                    <label>Data Final</label>
                    <input name="periodo_fim" type="date" value="<?= $dados['periodo_fim'] ?>" class="w3-input w3-border w3-round" required>
                </div>
            </div>

            <!-- Botões -->
            <div class="w3-row-padding w3-margin-top">
                <div class="w3-col">
                    <button type="submit" class="w3-button w3-green w3-round w3-margin-bottom w3-margin-top w3-margin-right">
                        <span class="material-symbols-outlined">save</span>
                        <b>Salvar Lote</b>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="w3-container w3-padding  w3-margin-bottom">

    <div class="w3-card w3-white w3-padding w3-margin-top w3-round-large">
        <div class="w3-row w3-margin-bottom  w3-margin-top">
            <div class="w3-col s12 m12 l12">
                <h4>Sessões vinculadas ao lote</h4>
            </div>
        </div>
        <div class="w3-responsive ">
            <table class="w3-table-all w3-hoverable w3-striped w3-margin-bottom">
                <thead>
                    <tr class="w3-blue-gray w3-text-white">
                        <td><b>ID</b></td>
                        <td><b>PACIENTE</b></td>
                        <td><b>PSICÓLOGO</b></td>
                        <td><b>DATA</b></td>
                        <td><b>HORA</b></td>
                        <td><b>VALOR</b></td>
                        <td><b>STATUS</b></td>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($sessoes_vinculadas as $sessao): ?>
                        <tr>
                            <td><?= $sessao['id'] ?></td>
                            <td><?= $sessao['paciente'] ?></td>
                            <td><?= $sessao['psico'] ?></td>
                            <td><?= $sessao['data_agendada'] ?></td>
                            <td><?= $sessao['hora_agendada'] ?></td>
                            <td><?= number_format($sessao['valor_repasse'], 2) ?></td>
                            <td><?= $sessao['status'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <br>
    </div>
</div>

<?php
include_once __DIR__ . '/../../includes/footer.php';
?>