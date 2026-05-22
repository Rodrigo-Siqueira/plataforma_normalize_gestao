<?php

require_once __DIR__ . '/../../config/config.php';
require_once 'regras_despesa.php';

$dados = array(
    "id" => '',
    "descricao" => '',
    "tipo" => 'VARIAVEL',
    "categoria" => '',
    "valor" => '',
    "data_competencia" => '',
    "data_pagamento" => '',
    "status" => 'PENDENTE',
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

            $resultado = adicionar_despesa($dados);
        } else {
            $resultado = alterar_despesa($dados);
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
        $resultado = buscar_despesa_id($id);

        if ($resultado) {
            $dados['id'] = $resultado['id'];
            $dados['descricao'] = $resultado['descricao'];
            $dados['tipo'] = $resultado['tipo'];
            $dados['categoria'] = $resultado['categoria'];
            $dados['valor'] = $resultado['valor'];
            $dados['data_competencia'] = $resultado['data_competencia'];
            $dados['data_pagamento'] = $resultado['data_pagamento'];
            $dados['status'] = $resultado['status'];
        }
    }
}

$currentPage = 'despesas';
include_once __DIR__ . '/../../includes/header.php';
?>

<div class="w3-container w3-padding  w3-margin-bottom">
    <div class="w3-container w3-padding  w3-margin-bottom">
        <h3>Formulário de Despesas</h3>
    </div>

    <div class="w3-card w3-white w3-padding w3-margin-top w3-round" style="max-width:750px; margin:auto;">
        <form enctype="multipart/form-data" method="post" action="despesa_form.php?action=save" class="w3-container">

            <div class="w3-row-padding w3-margin-top">
                <div class="w3-third">
                    <!-- Código -->
                    <label>Código</label>
                    <input name="id" readonly type="text" value="<?= $dados['id'] ?>" class="w3-input w3-border w3-sand w3-round">
                </div>
                <div class="w3-third w3-right">
                    <!-- Status -->
                    <label>Status</label>
                    <select name="status" class="w3-select w3-border w3-margin-bottom w3-round" required>
                        <option value="">Selecione...</option>
                        <option value="PENDENTE" <?= ($dados['status'] == 'PENDENTE') ? 'selected' : '' ?>>Pendente</option>
                        <option value="PAGO" <?= ($dados['status'] == 'PAGO') ? 'selected' : '' ?>>Pago</option>
                    </select>
                </div>
            </div>

            <div class="w3-row-padding w3-margin-top">
                <div class="w3-col">
                    <!-- Descrição -->
                    <label>Descrição</label>
                    <input name="descricao" type="text" value="<?= $dados['descricao'] ?>" class="w3-input w3-border w3-round" required>
                </div>
            </div>

            <div class="w3-row-padding w3-margin-top">
                <div class="w3-half">
                    <!-- Tipo -->
                    <label>Tipo</label>
                    <select name="tipo" class="w3-select w3-border w3-round" required>
                        <option value="">Selecione...</option>
                        <option value="FIXA" <?= ($dados['tipo'] == 'FIXA') ? 'selected' : '' ?>>Fixa</option>
                        <option value="VARIAVEL" <?= ($dados['tipo'] == 'VARIAVEL') ? 'selected' : '' ?>>Variavel</option>
                    </select>
                </div>
                <div class="w3-half">
                    <!-- Categoria -->
                    <label>Categoria</label>
                    <select name="categoria" class="w3-select w3-border w3-round" required>
                        <option value="">Selecione...</option>
                        <option value="MARKETING" <?= ($dados['categoria'] == 'MARKETING') ? 'selected' : '' ?>>Marketing</option>
                        <option value="SOFTWARE" <?= ($dados['categoria'] == 'SOFTWARE') ? 'selected' : '' ?>>Software</option>
                        <option value="IMPOSTOS" <?= ($dados['categoria'] == 'IMPOSTOS') ? 'selected' : '' ?>>Impostos</option>
                        <option value="CONTABILIDADE" <?= ($dados['categoria'] == 'CONTABILIDADE') ? 'selected' : '' ?>>Contabilidade</option>
                        <option value="INTERNET" <?= ($dados['categoria'] == 'INTERNET') ? 'selected' : '' ?>>Internet</option>
                        <option value="GOOGLE ADS" <?= ($dados['categoria'] == 'GOOGLE ADS') ? 'selected' : '' ?>>Google Ads</option>
                    </select>
                </div>
            </div>

            <div class="w3-row-padding w3-margin-top">
                <div class="w3-third">
                    <!-- Valor -->
                    <label>Valor</label>
                    <input name="valor" type="number" value="<?= $dados['valor'] ?>" step="0.01" class="w3-input w3-border w3-round" required>
                </div>
                <div class="w3-third">
                    <!-- Data da Despesa -->
                    <label>Data da Despesa</label>
                    <input name="data_competencia" type="date" value="<?= $dados['data_competencia'] ?>" class="w3-input w3-border w3-round" required>
                </div>
                <div class="w3-third">
                    <!-- Data do Pagamento -->
                    <label>Data do Pagamento</label>
                    <input name="data_pagamento" type="date" value="<?= $dados['data_pagamento'] ?>" class="w3-input w3-border w3-round">
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