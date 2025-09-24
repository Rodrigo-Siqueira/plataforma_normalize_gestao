<?php
require_once 'regras_produto.php';
$currentPage = 'produtos';

$dados = array(
    "id" => '',
    "nome" => '',
    "tipo" => '',
    "sessoes" => '',
    "preco" => 0,
    "validade" => 30,
    "ativo" => 1,
);

if (!empty($_REQUEST['action'])) {

    if ($_REQUEST['action'] == 'save') {
        $dados = $_POST;

        if (empty($dados['id'])) {

            # Chamar a função para adicionar o produto
            $resultado = adicionar_produto($dados);
        } else {

            # Chamar a função para atualisar o produto
            $resultado = alterar_produto($dados);
        }

        if ($resultado['success']) {
            print "<script>
                    alert('" . addslashes($resultado['message']) . "');
                    window.location.href = 'index.php';
                </script>";
        } else {
            print "<script>
                    alert('" . addslashes($resultado['error']) . "');
                    window.location.href = 'index.php';
                </script>";
        }
    } elseif ($_REQUEST['action'] == 'edit') {
        $id = (int) $_GET['id'];

        $resultado = buscar_produto_id($id);
        if ($resultado) {
            $dados['id'] = $resultado['id'];
            $dados['nome'] = $resultado['nome'];
            $dados['tipo'] = $resultado['tipo'];
            $dados['sessoes'] = $resultado['numero_sessoes'];
            $dados['preco'] = $resultado['preco'];
            $dados['validade'] = $resultado['validade_dias'];
            $dados['ativo'] = $resultado['ativo'];
        }
    }
}

include_once __DIR__ . '/../../includes/header.php';
?>

<div class="w3-container w3-padding  w3-margin-bottom">
    <div class="w3-container w3-padding  w3-margin-bottom">
        <h3>Formulário de Produto</h3>
    </div>

    <div class="w3-card w3-white w3-padding w3-margin-top w3-round" style="max-width:750px; margin:auto;">
        <form enctype="multipart/form-data" method="post" action="prod_form.php?action=save" class="w3-container">

            <div class="w3-row-padding w3-margin-top">
                <!-- Código -->
                <div class="w3-quarter">
                    <label>Código</label>
                    <input name="id" readonly type="text" value="<?= $dados['id'] ?>" class="w3-input w3-border w3-sand w3-round">
                </div>

                <div class="w3-threequarter">
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
                    <!-- Nome -->
                    <label>Nome</label>
                    <input name="nome" type="text" value="<?= $dados['nome'] ?>" class="w3-input w3-border w3-round" required>
                </div>
            </div>

            <div class="w3-row-padding w3-margin-top">
                <div class="w3-half">
                    <!-- Tipo -->
                    <label>Tipo</label>
                    <select name="tipo" class="w3-select w3-border w3-round" required>
                        <option value="">Selecione...</option>
                        <option value="PACOTE" <?= ($dados['tipo'] == 'PACOTE') ? 'selected' : '' ?>>Pacote</option>
                        <option value="AVULSO" <?= ($dados['tipo'] == 'AVULSO') ? 'selected' : '' ?>>Avulso</option>
                    </select>
                </div>

                <div class="w3-half">
                    <!-- Quantidade de Sessões -->
                    <label>Quantidade de Sessões</label>
                    <input name="sessoes" type="number" value="<?= $dados['sessoes'] ?>" class="w3-input w3-border w3-round" required>
                </div>
            </div>

            <div class="w3-row-padding w3-margin-top">
                <div class="w3-half">
                    <!-- Preço -->
                    <label>Preço</label>
                    <input name="preco" type="number" value="<?= number_format($dados['preco'], 2) ?>" step="0.01" class="w3-input w3-border w3-round" required>
                </div>

                <div class="w3-half">
                    <!-- Validade -->
                    <label>Validade</label>
                    <input name="validade" type="number" value="<?= $dados['validade'] ?>" class="w3-input w3-border w3-round" required>
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