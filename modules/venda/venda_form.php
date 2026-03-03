<?php
require_once __DIR__ . '/../../config/config.php';
require_once 'regras_venda.php';
require_once '../paciente/regras_paciente.php';
require_once '../resp_financeiro/regras_resp_financeiro.php';
require_once '../produto/regras_produto.php';

$pacientes = listar_pacientes();
$produtos = listar_produtos();

$dados = array(
    'id' => '',
    'paciente_id' => '',
    'responsavel_id' => '',
    'produto_id' => '',
    'preco' => '',
    'forma_pagamento' => 'PIX',
    'status' => 'PENDENTE',
    'data_venda' => ''
);

if (!empty($_REQUEST['action'])) {
    if ($_REQUEST['action'] == 'save') {
        $dados = $_POST;

        if (empty($dados['id'])) {
            $resultado = adicionar_venda($dados);
        } else {

            $resultado = alterar_venda($dados);
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
        $resultado = buscar_venda_id($id);

        if ($resultado) {
            $dados['id'] = $resultado['id'];
            $dados['paciente_id'] = $resultado['paciente_id'];
            $dados['responsavel_id'] = $resultado['responsavel_id'];
            $dados['produto_id'] = $resultado['produto_id'];
            $dados['preco'] = $resultado['valor_cobrado'];
            $dados['status'] = $resultado['status_pagamento'];
            $dados['forma_pagamento'] = $resultado['forma_pagamento'];
            $dados['data_venda'] = $resultado['data_venda'];
        }
    }
}

$currentPage = 'vendas';
include_once __DIR__ . '/../../includes/header.php';
?>

<div class="w3-container w3-padding  w3-margin-bottom">
    <div class="w3-container w3-padding  w3-margin-bottom">
        <h3>Registrar Vendas</h3>
    </div>
    <div class="w3-card w3-white w3-padding w3-margin-top w3-round" style="max-width:750px; margin:auto;">
        <form enctype="multipart/form-data" method="post" action="venda_form.php?action=save" class="w3-container">

            <div class="w3-row-padding w3-margin-top">
                <div class="w3-third">
                    <!-- Código -->
                    <label>Código</label>
                    <input name="id" readonly type="text" value="<?= $dados['id'] ?>" class="w3-input w3-border w3-sand w3-round">
                </div>
                <div class="w3-third w3-right">
                    <!-- Status -->
                    <label>Status</label>
                    <select name="status" class="w3-select w3-border w3-round" required>
                        <option value="">Selecione...</option>
                        <option value="PENDENTE" <?= ($dados['status'] == 'PENDENTE') ? 'selected' : '' ?>>PENDENTE</option>
                        <option value="PAGO" <?= ($dados['status'] == 'PAGO') ? 'selected' : '' ?>>PAGO</option>
                        <option value="ATRASADO" <?= ($dados['status'] == 'ATRASADO') ? 'selected' : '' ?>>ATRASADO</option>
                        <option value="CANCELADO" <?= ($dados['status'] == 'CANCELADO') ? 'selected' : '' ?>>CANCELADO</option>
                    </select>
                </div>
            </div>
            <div class="w3-row-padding w3-margin-top">
                <div class="w3-col">
                    <!-- Paciente -->
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
            <div class="w3-row-padding w3-margin-top">
                <div class="w3-col">
                    <!-- Responsável Financeiro -->
                    <label>Responsável Financeiro</label>
                    <select name="responsavel_id" id="responsavel_id" class="w3-select w3-border w3-round">
                        <option value="">Selecione...</option>
                        <?php
                        if ($pacientes) {
                            // Use um array para rastrear IDs de responsáveis já adicionados
                            $responsaveis_adicionados = [];

                            foreach ($pacientes as $paciente) {
                                $id_resp = $paciente['id_resp'];
                                $nome_resp = $paciente['responsavel'];

                                // Verifica se este responsável já foi adicionado para evitar duplicatas
                                if (!in_array($id_resp, $responsaveis_adicionados)) {

                                    // Verifica se este é o responsável da venda que está sendo editada
                                    $selected = ($id_resp == $dados['responsavel_id']) ? 'selected' : '';

                                    print "<option value='{$id_resp}' $selected>{$nome_resp}</option>";

                                    // Adiciona o ID do responsável à lista de adicionados
                                    $responsaveis_adicionados[] = $id_resp;
                                }
                            }
                        }
                        ?>
                    </select>
                </div>
            </div>
            <!-- Script para adicionar o responsavel financeiro ao selecionar o paciente -->
            <script>
                const mapaResponsaveis = <?= json_encode(
                                                array_column($pacientes, 'id_resp', 'id'),
                                                JSON_UNESCAPED_UNICODE
                                            ) ?>;

                document.addEventListener("DOMContentLoaded", function() {
                    const pacienteSelect = document.querySelector("select[name='paciente_id']");
                    const responsavelSelect = document.getElementById("responsavel_id");

                    pacienteSelect.addEventListener("change", function() {
                        let pacienteId = this.value;
                        let responsavelId = mapaResponsaveis[pacienteId];

                        if (responsavelId) {
                            for (let opt of responsavelSelect.options) {
                                opt.selected = (opt.value == responsavelId);
                            }
                        } else {
                            responsavelSelect.value = "";
                        }
                    });
                });
            </script>

            <div class="w3-row-padding w3-margin-top">
                <div class="w3-threequarter">
                    <!-- Produto  -->
                    <label>Produto</label>
                    <select name="produto_id" id="produto_id" class="w3-select w3-border w3-round" required onchange="atualizarPreco()">
                        <option value="">Selecione...</option>
                        <?php
                        if ($produtos) {
                            foreach ($produtos as $produto) {
                                $selected = ($produto['id'] == $dados['produto_id']) ? 'selected' : '';
                                print "<option value='{$produto['id']}' data-preco='{$produto['preco']}' $selected>{$produto['nome']}</option>";
                            }
                        }
                        ?>
                    </select>
                </div>
                <div class="w3-quarter">
                    <!-- Preço -->
                    <label>Preço</label>
                    <input name="preco" type="number" id="preco" value="<?= $dados['preco'] ?>" class="w3-input w3-border w3-round" required>
                    <script>
                        function atualizarPreco() {
                            let select = document.getElementById("produto_id");
                            let precoInput = document.getElementById("preco");
                            let preco = select.options[select.selectedIndex].getAttribute("data-preco");
                            precoInput.value = preco ? preco : "";
                        }
                    </script>
                </div>
            </div>

            <div class="w3-row-padding w3-margin-top">
                <div class="w3-half">
                    <!-- Data de Inicio -->
                    <label>Data da Venda</label>
                    <input name="data_venda" type="date" value="<?= $dados['data_venda'] ?>" class="w3-input w3-border w3-round">
                </div>
                <div class="w3-half">
                    <!-- Forma de Pagamento -->
                    <label>Forma de Pagamento</label>
                    <select name="forma_pagamento" class="w3-select w3-border w3-round" required>
                        <option value="">Selecione...</option>
                        <option value="PIX" <?= ($dados['forma_pagamento'] == 'PIX') ? 'selected' : '' ?>>Pix</option>
                        <option value="CARTAO_CREDITO" <?= ($dados['forma_pagamento'] == 'CARTAO_CREDITO') ? 'selected' : '' ?>>Cartão de Crédito</option>
                    </select>
                </div>
            </div>

            <div class="w3-row-padding w3-margin-top">
                <div class="w3-col">
                    <!-- Botões -->
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