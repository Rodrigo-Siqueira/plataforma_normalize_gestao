<?php
require_once 'regras_despesa.php';

$despesas = listar_despesas();

$currentPage = 'despesas';
include_once __DIR__ . '/../../includes/header.php';
?>

<div class="w3-container w3-padding  w3-margin-bottom">
    <h3>Gestão de Despesas</h3>
    <div class="w3-card w3-white w3-padding w3-margin-top w3-round-large">
        <div class="w3-row w3-margin-bottom  w3-margin-top">
            <div class="w3-col s12 m12 l12 w3-right-align">
                <a href="despesa_form.php" class="w3-button w3-green w3-round">
                    <span class="material-symbols-outlined">add</span>
                    <b>Nova Despesa</b>
                </a>
            </div>
        </div>
        <div class="w3-responsive ">
            <?php if ($despesas): ?>
                <table class="w3-table-all w3-hoverable w3-striped w3-margin-bottom">
                    <thead>
                        <tr class="w3-theme-l1">
                            <td><b>AÇÕES</b></td>
                            <td><b>ID</b></td>
                            <td><b>DESCRIÇÃO</b></td>
                            <td><b>TIPO</b></td>
                            <td><b>CATEGORIA</b></td>
                            <td><b>VALOR</b></td>
                            <td><b>DATA DA DESP.</b></td>
                            <td><b>DATA DO PGTO</b></td>
                            <td><b>STATUS</b></td>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($despesas as $despesa): ?>
                            <tr>
                                <td>
                                    <a href="despesa_form.php?action=edit&id=<?= $despesa['id'] ?>" class="w3-text-blue w3-small">
                                        <span class="material-symbols-outlined">edit</span>
                                    </a>
                                </td>
                                <td><?= $despesa['id'] ?></td>
                                <td><?= $despesa['descricao'] ?></td>
                                <td><?= $despesa['tipo'] ?></td>
                                <td><?= $despesa['categoria'] ?></td>
                                <td><?= $despesa['valor'] ?></td>
                                <td><?= $despesa['data_competencia'] ?></td>
                                <td><?= $despesa['data_pagamento'] ?></td>
                                <td><?= $despesa['status'] ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="w3-center">Não existem Despesas adicionadas.</p>
            <?php endif; ?>
        </div>
        <br>
    </div>
</div>

<?php
include_once __DIR__ . '/../../includes/footer.php';
?>