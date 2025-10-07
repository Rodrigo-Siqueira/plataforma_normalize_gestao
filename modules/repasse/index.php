<?php
require_once 'regras_repasse.php';

$repasses = listar_repasses();

$currentPage = 'repasse';
include_once __DIR__ . '/../../includes/header.php';
?>

<div class="w3-container w3-padding  w3-margin-bottom">
    <h3>Gestão de Repasses</h3>
    <div class="w3-card w3-white w3-padding w3-margin-top w3-round-large">
        <div class="w3-row w3-margin-bottom  w3-margin-top">
            <div class="w3-col s12 m12 l12 w3-right-align">
                <a href="repasse_form.php" class="w3-button w3-green w3-round">
                    <span class="material-symbols-outlined">add</span>
                    <b>Novo Repasse</b>
                </a>
            </div>
        </div>
        <div class="w3-responsive ">
            <?php if ($repasses): ?>
            <table class="w3-table-all w3-hoverable w3-striped w3-margin-bottom">
                <thead>
                    <tr class="w3-theme-l1">
                        <td><b>AÇÕES</b></td>
                        <td><b>ID</b></td>
                        <td><b>PSICÓLOGO</b></td>
                        <td><b>DATA INICIAL</b></td>
                        <td><b>DATA FINAL</b></td>
                        <td><b>VALOR PAGO</b></td>
                        <td><b>STATUS</b></td>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($repasses as $repasse): ?>
                        <tr>
                            <td>
                                <a href="update_repasse_form.php?action=edit&id=<?= $repasse['id'] ?>" class="w3-text-blue w3-small">
                                    <span class="material-symbols-outlined">edit</span>
                                </a>
                            </td>
                            <td><?= $repasse['id'] ?></td>
                            <td><?= $repasse['nome_completo'] ?></td>
                            <td><?= $repasse['periodo_inicio'] ?></td>
                            <td><?= $repasse['periodo_fim'] ?></td>
                            <td><?= $repasse['valor_liquido_pago'] ?></td>
                            <td><?= $repasse['status'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
                <p class="w3-center">Não existe Repasse cadastrado.</p>
            <?php  endif; ?>
        </div>
        <br>
    </div>
</div>

<?php
include_once __DIR__ . '/../../includes/footer.php';
?>