<?php
require_once 'regras_paciente.php';

$pacientes = listar_pacientes();

$currentPage = 'paciente';
include_once __DIR__ . '/../../includes/header.php';
?>

<div class="w3-container w3-padding  w3-margin-bottom">
    <h3>Gestão de Pacientes</h3>
    <div class="w3-card w3-white w3-padding w3-margin-top w3-round-large">
        <div class="w3-row w3-margin-bottom  w3-margin-top">
            <div class="w3-col s12 m12 l12 w3-right-align">
                <a href="paciente_form.php" class="w3-button w3-green w3-round">
                    <span class="material-symbols-outlined">add</span>
                    <b>Novo Paciente</b>
                </a>
            </div>
        </div>
        <div class="w3-responsive ">
            <?php if ($pacientes): ?>
                <table class="w3-table-all w3-hoverable w3-striped w3-margin-bottom">
                    <thead>
                        <tr class="w3-theme-l1">
                            <td><b>AÇÕES</b></td>
                            <td><b>ID</b></td>
                            <td><b>NOME</b></td>
                            <td><b>CONTATO</b></td>
                            <td><b>PSICÓLOGO</b></td>
                            <td><b>RESP. FIN.</b></td>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pacientes as $paciente): ?>
                            <tr>
                                <td>
                                    <a href="paciente_form.php?action=edit&id=<?= $paciente['id'] ?>" class="w3-text-blue w3-small">
                                        <span class="material-symbols-outlined">edit</span>
                                    </a>
                                </td>
                                <td><?= $paciente['id'] ?></td>
                                <td><?= $paciente['paciente'] ?></td>
                                <td><?= $paciente['contato'] ?></td>
                                <td><?= $paciente['psicologo'] ?></td>
                                <td><?= $paciente['responsavel'] ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="w3-center">Não existe Pacientes cadastrados.</p>
            <?php endif; ?>
        </div>
        <br>
    </div>
</div>

<?php
include_once __DIR__ . '/../../includes/footer.php';
?>