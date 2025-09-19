<?php
$currentPage = 'vendas';
include_once __DIR__ . '/../../includes/header.php';

$dados = array(
    'id' => '',
    'paciente_id' => '',
    'responsavel_id' => '',
    'produto_id' => '',
    'preco' => '',
    'status' => 'PENDENTE',
    'data_pagamento' => ''
);

$conn = mysqli_connect(
    'localhost',
    'root',
    '',
    'dev_agendamento_db'
) or die('Erro de conexão');

if (!empty($_REQUEST['action'])) {
    if ($_REQUEST['action'] == 'save') {
        $dados = $_POST;

        if (empty($dados['id'])) {
            if (empty($dados['data_pagamento'])) {
                $sql = "INSERT INTO vendas (
                paciente_id,
                responsavel_id,
                produto_id,
                valor_cobrado,
                status_pagamento) ";
                $sql .= "VALUES (
                '{$dados['paciente_id']}', 
                '{$dados['responsavel_id']}',
                '{$dados['produto_id']}',
                '{$dados['preco']}',
                '{$dados['status']}')";

                $result = mysqli_query($conn, $sql);
            } else {
                $sql = "INSERT INTO vendas (
                paciente_id,
                responsavel_id,
                produto_id,
                valor_cobrado,
                status_pagamento, 
                data_pagamento) ";
                $sql .= "VALUES (
                '{$dados['paciente_id']}', 
                '{$dados['responsavel_id']}',
                '{$dados['produto_id']}',
                '{$dados['preco']}',
                '{$dados['status']}', 
                '{$dados['data_pagamento']}')";

                $result = mysqli_query($conn, $sql);
            }
        } else {
            $sql = "UPDATE vendas SET";
            $sql .= " paciente_id = '{$dados['paciente_id']}',";
            $sql .= " responsavel_id = '{$dados['responsavel_id']}',";
            $sql .= " produto_id = '{$dados['produto_id']}',";
            $sql .= " valor_cobrado = '{$dados['preco']}',";
            $sql .= " status_pagamento = '{$dados['status']}',";
            $sql .= " data_pagamento = '{$dados['data_pagamento']}'";
            $sql .= " WHERE id = '{$dados['id']}'";

            $result = mysqli_query($conn, $sql);
        }

        print ($result) ? 'Registro salvo com sucesso.' : mysqli_error($conn);
        header('location: index.php');
    } elseif ($_REQUEST['action'] == 'edit') {
        $id = $_GET['id'];

        $sql = "SELECT * FROM vendas WHERE id = '{$id}'";
        $result = mysqli_query($conn, $sql);

        if ($row = mysqli_fetch_assoc($result)) {
            $dados['id'] = $row['id'];
            $dados['paciente_id'] = $row['paciente_id'];
            $dados['responsavel_id'] = $row['responsavel_id'];
            $dados['produto_id'] = $row['produto_id'];
            $dados['preco'] = $row['valor_cobrado'];
            $dados['status'] = $row['status_pagamento'];
            $dados['data_pagamento'] = $row['data_pagamento'];
        }
    }
}

?>

<div class="w3-container w3-padding  w3-margin-bottom">
    <div class="w3-container w3-padding  w3-margin-bottom">
        <h3>Formulário de Vendas</h3>
    </div>
    <div class="w3-card w3-white w3-padding w3-margin-top w3-round" style="max-width:750px; margin:auto;">
        <form enctype="multipart/form-data" method="post" action="venda_form.php?action=save" class="w3-container">

            <!-- Código -->
            <label>Código</label>
            <input name="id" readonly type="text" style="width: 25%" value="<?= $dados['id'] ?>" class="w3-input w3-border w3-sand w3-round">

            <!-- Paciente -->
            <label>Paciente</label>
            <select name="paciente_id" class="w3-select w3-border w3-round" required>
                <option value="">Selecione...</option>

                <?php
                $sql = "SELECT id, nome_completo FROM pacientes WHERE ativo = '1'";
                $result = mysqli_query($conn, $sql);
                if ($result) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        $selected = ($row['id'] == $dados['paciente_id']) ? 'selected' : '';
                        print "<option value='{$row['id']}' $selected>{$row['nome_completo']}</option>";
                    }
                }
                ?>
            </select>

            <!-- Responsável Financeiro -->
            <label>Responsável Financeiro</label>
            <select name="responsavel_id" class="w3-select w3-border w3-round" required>
                <option value="">Selecione...</option>

                <?php
                $sql = "SELECT id, nome_completo FROM responsaveis_financeiros WHERE ativo = '1'";
                $result = mysqli_query($conn, $sql);
                if ($result) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        $selected = ($row['id'] == $dados['responsavel_id']) ? 'selected' : '';
                        print "<option value='{$row['id']}' $selected>{$row['nome_completo']}</option>";
                    }
                }
                ?>
            </select>

            <!-- Produto  -->
            <label>Produto</label>
            <select name="produto_id" id="produto_id" class="w3-select w3-border w3-round" required onchange="atualizarPreco()">
                <option value="">Selecione...</option>

                <?php
                $sql = "SELECT id, nome, preco FROM produtos WHERE ativo = '1'";
                $result = mysqli_query($conn, $sql);
                if ($result) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        $selected = ($row['id'] == $dados['produto_id']) ? 'selected' : '';
                        print "<option value='{$row['id']}' data-preco='{$row['preco']}' $selected>{$row['nome']}</option>";
                    }
                }
                ?>
            </select>

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

            <!-- Status -->
            <label>Status</label>
            <select name="status" class="w3-select w3-border w3-margin-bottom w3-round" required>
                <option value="">Selecione...</option>
                <option value="PENDENTE" <?= ($dados['status'] == 'PENDENTE') ? 'selected' : '' ?>>PENDENTE</option>
                <option value="PAGO" <?= ($dados['status'] == 'PAGO') ? 'selected' : '' ?>>PAGO</option>
                <option value="ATRASADO" <?= ($dados['status'] == 'ATRASADO') ? 'selected' : '' ?>>ATRASADO</option>
                <option value="CANCELADO" <?= ($dados['status'] == 'CANCELADO') ? 'selected' : '' ?>>CANCELADO</option>
            </select>

            <!-- Data de Inicio -->
            <label>Data do Pagamento</label>
            <input name="data_pagamento" type="date" value="<?= $dados['data_pagamento'] ?>" class="w3-input w3-border w3-round">

            <!-- Botões -->
            <button type="submit" class="w3-button w3-green w3-round w3-margin-bottom w3-margin-top w3-margin-right">
                <span class="material-symbols-outlined">save</span>
                <b>Salvar</b>
            </button>
            <button type="reset" class="w3-button w3-red w3-round w3-margin-bottom w3-margin-top w3-margin-right">
                <span class="material-symbols-outlined">cancel</span>
                <b>Cancelar</b>
            </button>
        </form>
    </div>
</div>


<?php
mysqli_close($conn);
include_once __DIR__ . '/../../includes/footer.php';
?>