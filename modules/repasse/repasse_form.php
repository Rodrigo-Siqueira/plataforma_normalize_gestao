<?php
require_once 'regras_repasse.php';
require_once '../sessao/regras_sessao.php';
require_once '../psicologo/regras_psicologo.php';

$sessoes_repasse = listar_sessoes_disp_repasse();
$psicologos = listar_psicologo();

$dados = array(
    'id' => '',
    'psicologo_id' => '',
    'periodo_inicio' => '',
    'periodo_fim' => ''
);

if (!empty($_REQUEST['action'])) {
    if ($_REQUEST['action'] == 'save') {
        $dados = $_POST;

        if (empty($dados['id'])) {

            $sessoes = listar_sessoes_mes($dados);

            if (empty($sessoes)) {
                print "<script>alert('Nenhuma sessão a ser paga para este psicólogo no período selecionado.');</script>";
            } else {
                
                $resultado = gerar_repasse($dados, $sessoes);

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
        }
    }
}

$currentPage = 'repasse';
include_once __DIR__ . '/../../includes/header.php';
?>

<div class="w3-container w3-padding  w3-margin-bottom">
    <div class="w3-container w3-padding  w3-margin-bottom">
        <h3>Cadastro de Repasse</h3>
    </div>
    <div class="w3-card w3-white w3-padding w3-margin-top w3-round" style="max-width:750px; margin:auto;">
        <form enctype="multipart/form-data" method="post" action="repasse_form.php?action=save">

            <!-- Psicólogo -->
            <div class="w3-row-padding w3-margin-top">
                <div class="w3-col">
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
                        <b>Gerar Lote</b>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Lista de sessões disponíveis para repasse -->
<div class="w3-container w3-padding  w3-margin-bottom">

    <div class="w3-card w3-white w3-padding w3-margin-top w3-round-large">
        <div class="w3-row w3-margin-bottom  w3-margin-top">
            <div class="w3-col s12 m12 l12">
                <h4>Lista de sessões disponíveis para repasse</h4>
            </div>
        </div>
        <div class="w3-responsive ">
            <?php if ($sessoes_repasse): ?>
                <table class="w3-table-all w3-hoverable w3-striped w3-margin-bottom">
                    <thead>
                        <tr class="w3-theme-l1">
                            <td><b>ID</b></td>
                            <td><b>PACIENTE</b></td>
                            <td><b>PSICÓLOGO</b></td>
                            <td><b>DATA</b></td>
                            <td><b>HORA</b></td>
                            <td><b>STATUS</b></td>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($sessoes_repasse as $repasse): ?>
                            <tr>
                                <td><?= $repasse['id'] ?></td>
                                <td><?= $repasse['paciente'] ?></td>
                                <td><?= $repasse['nome_completo'] ?></td>
                                <td><?= $repasse['data_agendada'] ?></td>
                                <td><?= $repasse['hora_agendada'] ?></td>
                                <td><?= $repasse['status'] ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="w3-center">Não existem sessões disponíveis para repasse.</p>
            <?php endif; ?>
        </div>
        <br>
    </div>
</div>

<?php
include_once __DIR__ . '/../../includes/footer.php';
?>