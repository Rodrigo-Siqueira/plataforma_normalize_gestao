<?php
require_once __DIR__ . '/../../config/config.php';
require_once 'regras_venda.php';

$vendas = listar_vendas();

$currentPage = 'vendas';
include_once __DIR__ . '/../../includes/header.php';
?>

<div class="w3-container w3-padding  w3-margin-bottom">
    <h3>Gestão de Vendas</h3>
    <div class="w3-card w3-white w3-padding w3-margin-top w3-round-large">
        <div class="w3-row w3-margin-bottom  w3-margin-top">
            <div class="w3-col s12 m12 l12 w3-right-align">
                <a href="venda_form.php" class="w3-button w3-green w3-round">
                    <span class="material-symbols-outlined">add</span>
                    <b>Nova Vendas</b>
                </a>
            </div>
        </div>
        <div class="w3-responsive ">
            <?php if ($vendas): ?>
                <table class="w3-table-all w3-hoverable w3-striped w3-margin-bottom">
                    <thead>
                        <tr class="w3-theme-l1">
                            <td><b>AÇÕES</b></td>
                            <td><b>ID</b></td>
                            <td><b>PACIENTE</b></td>
                            <td><b>PRODUTO</b></td>
                            <td><b>PREÇO</b></td>
                            <td><b>DATA VENDA</b></td>
                            <td><b>STATUS</b></td>
                            <td><b>DATA PAG.</b></td>
                        </tr>
                    </thead>
                    <tbody>
                        <?php  foreach ($vendas as $venda):?>
                            <tr>
                                <td>
                                    <a href="venda_form.php?action=edit&id=<?= $venda['id'] ?>" class="w3-text-blue w3-small">
                                        <span class="material-symbols-outlined">edit</span>
                                    </a>
                                </td>
                                <td><?= $venda['id'] ?></td>
                                <td><?= $venda['nome_completo'] ?></td>
                                <td><?= $venda['prod_nome'] ?></td>
                                <td><?= $venda['valor_cobrado'] ?></td>
                                <td><?= $venda['data_venda'] ?></td>
                                <td><?= $venda['status_pagamento'] ?></td>
                                <td><?= $venda['data_pagamento'] ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="w3-center">Não existe Vendas registradas.</p>
            <?php endif; ?>
        </div>
        <br>
    </div>
</div>

<?php
include_once __DIR__ . '/../../includes/footer.php';
?>