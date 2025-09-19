<?php

$currentPage = 'repasse';
include_once __DIR__ . '/../../includes/header.php';

$dados = array(
    'id' => '',
    'psicologo_id' => '',
    'periodo_inicio' => '',
    'periodo_fim' => ''
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

            $sql = "SELECT 
                s.id as sessao_id,
                v.valor_cobrado,
                prod.numero_sessoes,
                p.percentual_comissao
            FROM sessoes s
            JOIN vendas v ON s.venda_origem_id = v.id
            JOIN psicologo p ON s.psicologo_id = p.id
            JOIN produtos prod ON prod.id = v.produto_id
            WHERE s.psicologo_id = '{$dados['psicologo_id']}'
            AND s.status = 'REALIZADA'
            AND s.lote_repasse_id IS NULL
            AND s.data_agendada BETWEEN '{$dados['periodo_inicio']}' AND '{$dados['periodo_fim']}'";

            $result = mysqli_query($conn, $sql);

            if (mysqli_num_rows($result) === 0) {
                print 'Nenhuma sessão a ser paga para este psicólogo no período selecionado.';
            } else {
                $valor_total_repasse = 0;
                $ids_sessoes_processadas = [];

                while ($row = mysqli_fetch_assoc($result)) {
                    $valor_total_repasse += ($row['valor_cobrado'] / $row['numero_sessoes']) * $row['percentual_comissao'];
                    $ids_sessoes_processadas[] = $row['sessao_id'];
                }

                $sql = "INSERT INTO lotes_repasse (
                    psicologo_id, 
                    periodo_inicio, 
                    periodo_fim, 
                    valor_liquido_pago) 
                    VALUES (
                        '{$dados['psicologo_id']}', 
                        '{$dados['periodo_inicio']}', 
                        '{$dados['periodo_fim']}', 
                        '{$valor_total_repasse}')";

                $result = mysqli_query($conn, $sql);

                $lote_id = mysqli_insert_id($conn);
                if (!$lote_id) {
                    print 'Erro ao criar o lote de repasse.';
                } else {
                    $ids = implode(",", $ids_sessoes_processadas);
                    $sql = "UPDATE sessoes SET";
                    $sql .= " lote_repasse_id = '{$lote_id}'";
                    $sql .= " WHERE id IN ({$ids})";

                    if (!mysqli_query($conn, $sql)) {
                        print "Erro ao atualizar as sessões: " . mysqli_error($conn);
                    } else {
                        print "Lote de repasse criado com sucesso e sessões atualizadas!";
                    }
                }
            }
        }
    }
}
?>

<div class="w3-container w3-padding  w3-margin-bottom">
    <div class="w3-container w3-padding  w3-margin-bottom">
        <h3>Formulário de Repasse</h3>
    </div>
    <div class="w3-card w3-white w3-padding w3-margin-top w3-round" style="max-width:750px; margin:auto;">
        <form enctype="multipart/form-data" method="post" action="repasse_form.php?action=save">

            <!-- Psicólogo -->
            <div class="w3-row-padding w3-margin-top">
                <div class="w3-col">
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
                        <b>Gerar Lote</b>
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
                <h4>Lista de sessões disponíveis para repasse</h4>
            </div>
        </div>
        <div class="w3-responsive ">
            <table class="w3-table-all w3-hoverable w3-striped w3-margin-bottom">
                <thead>
                    <tr class="w3-theme-l1">
                        <td><b>ID</b></td>
                        <td><b>PACIENTE</b></td>
                        <td><b>PSICÓLOGO</b></td>
                        <td><b>DATA</b></td>
                        <td><b>HORA</b></td>
                        <td><b>STATUS</b></td>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "
                        SELECT 
                        s.id, 
                        p.nome_completo AS paciente,
                        psi.nome_completo, 
                        s.data_agendada, 
                        s.hora_agendada, 
                        s.status
                    FROM sessoes AS s
                    JOIN pacientes AS p ON p.id = s.paciente_id
                    JOIN psicologo AS psi ON psi.id = s.psicologo_id
                    WHERE s.status = 'REALIZADA'
                    AND s.lote_repasse_id IS NULL
                    ORDER BY s.id";

                    $result  = mysqli_query($conn, $sql);

                    while ($row = mysqli_fetch_assoc($result)) {
                        $id = $row['id'];
                        $paciente = $row['paciente'];
                        $psico = $row['nome_completo'];
                        $data = $row['data_agendada'];
                        $hora = $row['hora_agendada'];
                        $status = $row['status'];
                    ?>
                        <tr>
                            <td><?= $id ?></td>
                            <td><?= $paciente ?></td>
                            <td><?= $psico ?></td>
                            <td><?= $data ?></td>
                            <td><?= $hora ?></td>
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