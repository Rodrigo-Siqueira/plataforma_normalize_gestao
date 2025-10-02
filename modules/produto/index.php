<?php
require_once 'regras_produto.php';

$produtos = listar_produtos();

$currentPage = 'produtos';
include_once __DIR__ . '/../../includes/header.php';
?>

<div class="w3-container w3-padding  w3-margin-bottom">
    <h3>Gestão de Produtos</h3>
    <div class="w3-card w3-white w3-padding w3-margin-top w3-round-large">
        <div class="w3-row w3-margin-bottom  w3-margin-top">
            <div class="w3-col s12 m12 l12 w3-right-align">
                <a href="prod_form.php" class="w3-button w3-green w3-round">
                    <span class="material-symbols-outlined">add</span>
                    <b>Novo Produto</b>
                </a>
            </div>
        </div>
        <div class="w3-responsive ">
            <?php if ($produtos): ?>
                <table class="w3-table-all w3-hoverable w3-striped w3-margin-bottom">
                    <thead>
                        <tr class="w3-theme-l1">
                            <td><b>AÇÕES</b></td>
                            <td><b>ID</b></td>
                            <td><b>NOME</b></td>
                            <td><b>TIPO</b></td>
                            <td><b>SESSÃO</b></td>
                            <td><b>PREÇO</b></td>
                            <td><b>VALIDADE</b></td>
                            <td><b>ATIVO</b></td>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($produtos as $produto): ?>
                            <tr>
                                <td>
                                    <a href="prod_form.php?action=edit&id=<?= $produto['id'] ?>" class="w3-text-blue w3-small">
                                        <i class="material-symbols-outlined">edit</i>
                                    </a>
                                </td>
                                <td><?= $produto['id'] ?></td>
                                <td><?= $produto['nome'] ?></td>
                                <td><?= $produto['tipo'] ?></td>
                                <td><?= $produto['numero_sessoes'] ?></td>
                                <td><?= 'R$ ' . number_format($produto['preco'], 2, ',') ?></td>
                                <td><?= $produto['validade_dias'] . ' Dias' ?></td>
                                <td><?= ($produto['ativo'] == 1) ? 'Sim' : 'Não' ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="w3-center">Não existe produtos cadastrados.</p>
            <?php endif; ?>
        </div>
        <br>
    </div>
</div>

<?php
include_once __DIR__ . '/../../includes/footer.php';
?>