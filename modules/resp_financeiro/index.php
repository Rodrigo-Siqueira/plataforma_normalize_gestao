<?php
require_once 'regras_resp_financeiro.php';

$responsaveis_finan = listar_resp_financeiros();

$currentPage = 'resp_financeiro';
include_once __DIR__ . '/../../includes/header.php';
?>

<div class="w3-container w3-padding  w3-margin-bottom">
    <h3>Gestão de Responsável Financeiro</h3>
    <div class="w3-card w3-white w3-padding w3-margin-top w3-round-large">
        <div class="w3-row w3-margin-bottom  w3-margin-top">
            <div class="w3-col s12 m12 l12 w3-right-align">
                <a href="resp_financeiro_form.php" class="w3-button w3-green w3-round">
                    <span class="material-symbols-outlined">add</span>
                    <b>Novo Resp. Financeiro</b>
                </a>
            </div>
        </div>
        <div class="w3-responsive ">
            <?php if ($responsaveis_finan): ?>
                <table class="w3-table-all w3-hoverable w3-striped w3-margin-bottom">
                    <thead>
                        <tr class="w3-theme-l1">
                            <td><b>AÇÕES</b></td>
                            <td><b>ID</b></td>
                            <td><b>NOME</b></td>
                            <td><b>CONTATO</b></td>
                            <td><b>É PACIENTE</b></td>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($responsaveis_finan as $responsavel): ?>
                            <tr>
                                <td>
                                    <a href="resp_financeiro_form.php?action=edit&id=<?= $responsavel['id'] ?>" class="w3-text-blue w3-small">
                                        <span class="material-symbols-outlined">edit</span>
                                    </a>
                                </td>
                                <td><?= $responsavel['id'] ?></td>
                                <td><?= $responsavel['nome_completo'] ?></td>
                                <td><?= $responsavel['contato'] ?></td>
                                <td><?= ($responsavel['eh_paciente'] == 1) ? 'Sim' : 'Não' ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="w3-center">Não existe Responsaveis Financeiro cadastrados.</p>
            <?php endif; ?>
        </div>
        <br>
    </div>
</div>

<?php
include_once __DIR__ . '/../../includes/footer.php';
?>