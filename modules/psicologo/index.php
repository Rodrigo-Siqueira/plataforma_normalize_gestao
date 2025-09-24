<?php
require_once 'regras_psicologo.php';

$psicologos = listar_psicologo();

$currentPage = 'psicologo';
include_once __DIR__ . '/../../includes/header.php';
?>

<div class="w3-container w3-padding  w3-margin-bottom">
    <h3>Gestão de Psicólogo</h3>
    <div class="w3-card w3-white w3-padding w3-margin-top w3-round-large">
        <div class="w3-row w3-margin-bottom  w3-margin-top">
            <div class="w3-col s12 m12 l12 w3-right-align">
                <a href="psicologo_form.php" class="w3-button w3-green w3-round">
                    <span class="material-symbols-outlined">add</span>
                    <b>Novo Psicólogo</b>
                </a>
            </div>
        </div>
        <div class="w3-responsive ">
            <?php if ($psicologos): ?>
                <table class="w3-table-all w3-hoverable w3-striped w3-margin-bottom">
                    <thead>
                        <tr class="w3-theme-l1">
                            <td><b>AÇÕES</b></td>
                            <td><b>ID</b></td>
                            <td><b>NOME</b></td>
                            <td><b>CRP</b></td>
                            <td><b>CONTATO</b></td>
                            <td><b>ATIVO</b></td>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($psicologos as $psicologo): ?>
                            <tr>
                                <td>
                                    <a href="psicologo_form.php?action=edit&id=<?= $psicologo['id'] ?>" class="w3-text-blue w3-small">
                                        <span class="material-symbols-outlined">edit</span>
                                    </a>
                                </td>
                                <td><?= $psicologo['id'] ?></td>
                                <td><?= $psicologo['nome_completo'] ?></td>
                                <td><?= $psicologo['contato'] ?></td>
                                <td><?= $psicologo['crp'] ?></td>
                                <td><?= ($psicologo['ativo'] == 1) ? 'Sim' : 'Não' ?></td>

                            </tr>
                        <?php endforeach ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="w3-center">Não existe psicologos cadastrados.</p>
            <?php endif ?>
        </div>
        <br>
    </div>
</div>

<?php
include_once __DIR__ . '/../../includes/footer.php';
?>