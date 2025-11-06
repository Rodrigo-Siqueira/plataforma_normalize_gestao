<?php
require_once '../paciente/regras_paciente.php';
require_once 'regras_sessao.php';

$pacientes = listar_pacientes();

$dados = array(
    'id' => '',
    'paciente_id' => '',
    'psicologo_id' => '',
    'venda_origem_id' => '',
    'data_agendada' => '',
    'hora_agendada' => '',
    'status' => 'AGENDADA'
);

if (!empty($_REQUEST['action'])) {
    if ($_REQUEST['action'] == 'save') {
        $dados = $_POST;

        if (empty($dados['id'])) {            
            $saldo = credito_sessoes_paga($dados) - debito_sessoes_registradas($dados);

            if ($saldo > 0) {

                $venda = buscar_proxima_venda_disponivel($dados);

                if ($venda) {
                    $dados['venda_origem_id'] = $venda['id'];
                    $resultado = adicionar_sessao($dados);

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

                } else {
                    print "<script>alert('Não foi encontrada nenhuma venda com saldo!');</script>";
                }
            } else {
                print "<script>alert('Paciente sem créditos disponíveis!');</script>";
            }
        } else {
            $dados = $_POST;
            $resultado = alterar_sessao($dados);

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
        $id = (int) $_GET['id'];
        $sessao = buscar_sessao_por_id($id);

        if ($sessao) {
            $dados['id'] = $sessao['id'];
            $dados['paciente_id'] = $sessao['paciente_id'];
            $dados['psicologo_id'] = $sessao['psicologo_id'];
            $dados['venda_origem_id'] = $sessao['venda_origem_id'];
            $dados['data_agendada'] = $sessao['data_agendada'];
            $dados['hora_agendada'] = $sessao['hora_agendada'];
            $dados['status'] = $sessao['status'];
        }
    }
}

$currentPage = 'sessao';
include_once __DIR__ . '/../../includes/header.php';
?>

<div class="w3-container w3-padding  w3-margin-bottom">
    <div class="w3-container w3-padding  w3-margin-bottom">
        <h3>Formulário de Sessão</h3>
    </div>
    <div class="w3-card w3-white w3-padding w3-margin-top w3-round" style="max-width:750px; margin:auto;">
        <form enctype="multipart/form-data" method="post" action="sessao_form.php?action=save">
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
                        <option value="AGENDADA" <?= ($dados['status'] == 'AGENDADA') ? 'selected' : '' ?>>Agendada</option>
                        <option value="REALIZADA" <?= ($dados['status'] == 'REALIZADA') ? 'selected' : '' ?>>Realizada</option>
                        <option value="REMARCADA" <?= ($dados['status'] == 'REMARCADA') ? 'selected' : '' ?>>Remarcada</option>
                        <option value="CANCELADA_PACIENTE" <?= ($dados['status'] == 'CANCELADA_PACIENTE') ? 'selected' : '' ?>>Paciente Cancelou</option>
                        <option value="CANCELADA_PSICOLOGO" <?= ($dados['status'] == 'CANCELADA_PSICOLOGO') ? 'selected' : '' ?>>Psicólogo Cancelou</option>
                    </select>
                </div>
            </div>

            <!-- Paciente -->
            <div class="w3-row-padding w3-margin-top">
                <div class="w3-col">
                    <label>Paciente</label>
                    <select name="paciente_id" class="w3-select w3-border w3-round" required>
                        <option value="">Selecione...</option>
                        <?php
                        if ($pacientes) {
                            foreach ($pacientes as $paciente) {
                                $selected = ($paciente['id'] == $dados['paciente_id']) ? 'selected' : '';
                                print "<option value='{$paciente['id']}' $selected>{$paciente['paciente']}</option>";
                            }
                        }
                        ?>
                    </select>
                </div>
            </div>

            <!-- Psicólogo -->
            <div class="w3-row-padding w3-margin-top">
                <div class="w3-col">
                    <label>Psicólogo</label>
                    <select name="psicologo_id" id="psicologo_id" class="w3-select w3-border w3-round" required>
                        <option value="">Selecione...</option>
                        <?php
                        if ($pacientes) {
                            // 1. Use um array para rastrear IDs de psicólogos já adicionados
                            $psicologos_adicionados = [];

                            foreach ($pacientes as $paciente) {
                                $id_psi = $paciente['id_psi'];
                                $nome_psi = $paciente['psicologo'];

                                // 2. Verifica se este psicólogo já foi adicionado para evitar duplicatas
                                if (!in_array($id_psi, $psicologos_adicionados)) {

                                    // 3. Inicializa $selected em cada iteração
                                    $selected = '';

                                    // 4. Verifica se este é o psicólogo da sessão que está sendo editada
                                    if ($id_psi == $dados['psicologo_id']) {
                                        $selected = 'selected';
                                    }

                                    print "<option value='{$id_psi}' $selected>{$nome_psi}</option>";

                                    // 5. Adiciona o ID do psicólogo à lista de adicionados
                                    $psicologos_adicionados[] = $id_psi;
                                }
                            }
                        }
                        ?>
                    </select>
                </div>
            </div>
            <!-- Script para adicionar o psicólogo ao selecionar o paciente -->
            <script>
                const mapapsicologo = <?= json_encode(
                                            array_column($pacientes, 'id_psi', 'id'),
                                            JSON_UNESCAPED_UNICODE
                                        ) ?>;

                document.addEventListener("DOMContentLoaded", function() {
                    const pacienteSelect = document.querySelector("select[name='paciente_id']");
                    const psicologoSelect = document.getElementById("psicologo_id");

                    pacienteSelect.addEventListener("change", function() {
                        let pacienteId = this.value;
                        let psicologoId = mapapsicologo[pacienteId];

                        if (psicologoId) {
                            for (let opt of psicologoSelect.options) {
                                opt.selected = (opt.value == psicologoId);
                            }
                        } else {
                            psicologoSelect.value = "";
                        }
                    });
                });
            </script>

            <!-- Data e Hora de Inicio -->
            <div class="w3-row-padding w3-margin-top">
                <div class="w3-half">
                    <label>Data da Sessão</label>
                    <input name="data_agendada" type="date" value="<?= $dados['data_agendada'] ?>" class="w3-input w3-border w3-round" required>
                </div>
                <div class="w3-half">
                    <label>Hora da Sessão</label>
                    <input name="hora_agendada" type="time" value="<?= $dados['hora_agendada'] ?>" class="w3-input w3-border w3-round" required>
                </div>
            </div>

            <!-- Botões -->
            <div class="w3-row-padding w3-margin-top">
                <div class="w3-col">
                    <button type="submit" class="w3-button w3-green w3-round w3-margin-bottom w3-margin-top w3-margin-right">
                        <span class="material-symbols-outlined">save</span>
                        <b>Salvar</b>
                    </button>
                    <button type="reset" class="w3-button w3-red w3-round w3-margin-bottom w3-margin-top w3-margin-right">
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