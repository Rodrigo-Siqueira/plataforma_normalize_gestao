<?php
require_once __DIR__ . '/../../config/config.php';
require_once 'regras_sessao.php';

$sessoes = listar_sessoes();

$currentPage = 'sessao';
include_once __DIR__ . '/../../includes/header.php';
?>

<div class="w3-container w3-padding  w3-margin-bottom">
    <h3>Gestão de Sessões</h3>
    <div class="w3-card w3-white w3-padding w3-margin-top w3-round-large">
        <div class="w3-row w3-margin-bottom  w3-margin-top">
            <div class="w3-col s12 m12 l12 w3-right-align">
                <a href="sessao_form.php" class="w3-button w3-green w3-round">
                    <span class="material-symbols-outlined">add</span>
                    <b>Nova Sessão</b>
                </a>
            </div>
        </div>
        <div class="w3-responsive ">
            <?php if ($sessoes): ?>
            <table class="w3-table-all w3-hoverable w3-striped w3-margin-bottom">
                <thead>
                    <tr class="w3-theme-l1">
                        <td><b>AÇÕES</b></td>
                        <td><b>ID</b></td>
                        <td><b>PACIENTE</b></td>
                        <td><b>PSICÓLOGO</b></td>
                        <td><b>CARIMBO</b></td>
                        <td><b>DATA</b></td>
                        <td><b>HORA</b></td>
                        <td><b>STATUS</b></td>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($sessoes as $sessao): ?>
                        <tr>
                            <td>
                                <a href="sessao_form.php?action=edit&id=<?= $sessao['id'] ?>" class="w3-text-blue w3-small">
                                    <span class="material-symbols-outlined">edit</span>
                                </a>
                            </td>
                            <td><?= $sessao['id'] ?></td>
                            <td><?= $sessao['paciente'] ?></td>
                            <td><?= $sessao['psicologo'] ?></td>
                            <td><?= $sessao['carimbo'] ?></td>
                            <td><?= $sessao['data_agendada'] ?></td>
                            <td><?= $sessao['hora_agendada'] ?></td>
                            <td><?= $sessao['status'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
                <p class="w3-center">Não existe sessão cadastrada.</p>
            <?php endif; ?>
        </div>
        <br>
    </div>
</div>

<?php
include_once __DIR__ . '/../../includes/footer.php';
?>