<?php

$currentPage = 'repasse';
include_once __DIR__ . '/../../includes/header.php';

$dados = array(
    'id' => '',
    'psicologo_id' => '',
    'periodo_inicio' => '',
    'periodo_fim' => '',
    'valor_liquido_pago' => '',
    'status' => ''
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
            print 'O lote não foi carregado.';
        } else {
            $sql = "UPDATE lotes_repasse SET 
                status = '{$dados['status']}', 
                data_pagamento = " . ($dados['status'] === 'PAGO' ? "NOW()" : "NULL") . "
                WHERE id = '{$dados['id']}'";

            if (mysqli_query($conn, $sql)) {
                print 'Lote alterado com sucesso';
            } else {
                print 'Não foi possivel alterar o lote.';
            }
        }
    } elseif ($_REQUEST['action'] == 'edit') {
        $id = $_GET['id'];
        $sql = "SELECT * FROM lotes_repasse WHERE id = '{$id}'";
        $result = mysqli_query($conn, $sql);

        if ($row = mysqli_fetch_assoc($result)) {
            $dados['id'] = $row['id'];
            $dados['psicologo_id'] = $row['psicologo_id'];
            $dados['periodo_inicio'] = $row['periodo_inicio'];
            $dados['periodo_fim'] = $row['periodo_fim'];
            $dados['valor_liquido_pago'] = $row['valor_liquido_pago'];
            $dados['status'] = $row['status'];
        }
    }
}
?>

<div class="w3-container w3-padding  w3-margin-bottom">
    <div class="w3-container w3-padding  w3-margin-bottom">
        <h3>Atualiza Repasse</h3>
    </div>
    <div class="w3-card w3-white w3-padding w3-margin-top w3-round" style="max-width:750px; margin:auto;">
        <form enctype="multipart/form-data" method="post" action="update_repasse_form.php?action=save">

            <!-- Código -->
            <div class="w3-row-padding w3-margin-top">
                <div class="w3-quarter">
                    <label>Código</label>
                    <input name="id" readonly type="text" value="<?= $dados['id'] ?>" class="w3-input w3-border w3-sand w3-round">
                </div>

                <!-- Status -->
                <div class="w3-threequarter">
                    <label>Status</label>
                    <select name="status" class="w3-select w3-border w3-margin-bottom w3-round" required>
                        <option value="">Selecione...</option>
                        <option value="ABERTO" <?= ($dados['status'] == 'ABERTO') ? 'selected' : '' ?>>Aberto</option>
                        <option value="PAGO" <?= ($dados['status'] == 'PAGO') ? 'selected' : '' ?>>Pago</option>
                    </select>
                </div>
            </div>

            <!-- Psicólogo -->
            <div class="w3-row-padding w3-margin-top">
                <div class="w3-threequarter">
                    <label>Psicólogo</label>
                    <select name="psicologo_id" class="w3-select w3-border w3-round" required>
                        <option value="">Selecione...</option>

                        <?php
                        $sql = "SELECT id, nome_completo FROM psicologo WHERE ativo = '1'";
                        $result = mysqli_query($conn, $sql);
                        if ($result) {
                            while ($row = mysqli_fetch_assoc($result)) {
                                $selected = ($row['id'] == $dados['psicologo_id']) ? 'selected' : '';
                                print "<option value='{$row['id']}' $selected>{$row['nome_completo']}</option>";
                            }
                        }
                        ?>
                    </select>
                </div>
                <div class="w3-quarter">
                    <label>Valor Total</label>
                    <input name="valor_liquido_pago" type="number" value="<?= $dados['valor_liquido_pago'] ?>" class="w3-input w3-border w3-round" required>
                </div>
            </div>

            <!-- Data Inicio -->
            <div class="w3-row-padding w3-margin-top">
                <div class="w3-half">
                    <label>Data Inicial</label>
                    <input name="periodo_inicio" type="date" value="<?= $dados['periodo_inicio'] ?>" class="w3-input w3-border w3-round" required>
                </div>
                <div class="w3-half">
                    <label>Data Final</label>
                    <input name="periodo_fim" type="date" value="<?= $dados['periodo_fim'] ?>" class="w3-input w3-border w3-round" required>
                </div>
            </div>

            <!-- Botões -->
            <div class="w3-row-padding w3-margin-top">
                <div class="w3-col">
                    <button type="submit" class="w3-button w3-green w3-round w3-margin-bottom w3-margin-top w3-margin-right">
                        <span class="material-symbols-outlined">save</span>
                        <b>Salvar Lote</b>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="w3-container w3-padding  w3-margin-bottom">

    <div class="w3-card w3-white w3-padding w3-margin-top w3-round-large">
        <div class="w3-row w3-margin-bottom  w3-margin-top">
            <div class="w3-col s12 m12 l12">
                <h4>Sessões vinculadas ao lote</h4>
            </div>
        </div>
        <div class="w3-responsive ">
            <table class="w3-table-all w3-hoverable w3-striped w3-margin-bottom">
                <thead>
                    <tr class="w3-blue-gray w3-text-white">
                        <td><b>ID</b></td>
                        <td><b>PACIENTE</b></td>
                        <td><b>PSICÓLOGO</b></td>
                        <td><b>DATA</b></td>
                        <td><b>HORA</b></td>
                        <td><b>VALOR SESSÃO</b></td>
                        <td><b>STATUS</b></td>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "
                        SELECT 
                            s.id, 
                            p.nome_completo AS paciente,
                            psi.nome_completo AS psico, 
                            s.data_agendada,
                            s.hora_agendada,
                            ((v.valor_cobrado / prod.numero_sessoes) * psi.percentual_comissao) AS valor_repasse,
                            s.status
                        FROM sessoes AS s
                        JOIN pacientes p ON p.id = s.paciente_id
                        JOIN psicologo psi ON psi.id = s.psicologo_id
                        JOIN vendas v ON s.venda_origem_id = v.id
                        JOIN produtos prod ON prod.id = v.produto_id
                        WHERE s.lote_repasse_id = '{$dados['id']}'
                        ORDER BY s.id";

                    $result  = mysqli_query($conn, $sql);

                    while ($row = mysqli_fetch_assoc($result)) {
                        $id = $row['id'];
                        $paciente = $row['paciente'];
                        $psico = $row['psico'];
                        $data = date('d-m-Y', strtotime($row['data_agendada']));
                        $hora = $row['hora_agendada'];
                        $valor = $row['valor_repasse'];
                        $status = $row['status'];
                    ?>
                        <tr>
                            <td><?= $id ?></td>
                            <td><?= $paciente ?></td>
                            <td><?= $psico ?></td>
                            <td><?= $data ?></td>
                            <td><?= $hora ?></td>
                            <td><?= number_format($valor, 2) ?></td>
                            <td><?= $status ?></td>
                        </tr>
                    <?php
                    }
                    ?>
                </tbody>
            </table>
        </div>
        <br>
    </div>
</div>

<?php
mysqli_close($conn);
include_once __DIR__ . '/../../includes/footer.php';
?>