<?php
$currentPage = 'sessao';
include_once __DIR__ . '/../../includes/header.php';

$dados = array(
    'id' => '',
    'paciente_id' => '',
    'psicologo_id' => '',
    'venda_origem_id' => '',
    'data_agendada' => '',
    'hora_agendada' => '',
    'status' => 'AGENDADA'
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

            # Soma todas as sessões de pacotes com pagamento 'PAGO'
            $sql = mysqli_query(
                $conn,
                "SELECT SUM(p.numero_sessoes) AS total_creditos
                 FROM vendas v
                 JOIN produtos p ON v.produto_id = p.id
                 WHERE v.paciente_id = '{$dados['paciente_id']}' 
                   AND v.status_pagamento = 'PAGO'"
            );
            $sql_creditos = mysqli_fetch_assoc($sql);
            $creditos = intval($sql_creditos['total_creditos'] ?? 0);

            # Conta quantas sessões já foram agendadas para esse paciente
            $sql = mysqli_query(
                $conn,
                "SELECT COUNT(id) AS total_debitos
                 FROM sessoes
                 WHERE paciente_id = '{$dados['paciente_id']}'"
            );
            $sql_debitos = mysqli_fetch_assoc($sql);
            $debitos = intval($sql_debitos['total_debitos'] ?? 0);
            $saldo = $creditos - $debitos;

            if ($saldo > 0) {
                // Esta query é mais complexa. Ela busca vendas pagas e verifica se o número de sessões
                // do produto é maior que o número de sessões já associadas àquela venda.
                $sql = " SELECT v.id
                    FROM vendas v
                    JOIN produtos p ON v.produto_id = p.id
                    LEFT JOIN sessoes s ON s.venda_origem_id = v.id
                    WHERE v.paciente_id = '{$dados['paciente_id']}' AND v.status_pagamento = 'PAGO'
                    GROUP BY v.id, p.numero_sessoes
                    HAVING COUNT(s.id) < p.numero_sessoes
                    ORDER BY v.data_venda ASC
                    LIMIT 1";
                $result = mysqli_query($conn, $sql);
                $row = mysqli_fetch_assoc($result);

                if ($row) {
                    $dados['venda_origem_id'] = $row['id'];

                    $sql = "INSERT INTO sessoes (
                        paciente_id,
                        psicologo_id,
                        venda_origem_id,
                        data_agendada,
                        hora_agendada,
                        status)";
                    $sql .= "
                    VALUES (
                    '{$dados['paciente_id']}', 
                    '{$dados['psicologo_id']}',
                    '{$dados['venda_origem_id']}',
                    '{$dados['data_agendada']}', 
                    '{$dados['hora_agendada']}',
                    '{$dados['status']}')";

                    if (mysqli_query($conn, $sql)) {
                        print "<script>alert('Sessão registrada com sucesso!');
                            window.location.href = 'index.php';
                            </script>";
                    } else {
                        print "<script>alert('Erro ao salvar sessão: " . mysqli_error($conn) . "');</script>";
                    }
                } else {
                    print "<script>alert('Não foi encontrada nenhuma venda com saldo!');</script>";
                }
            } else {
                print "<script>alert('Paciente sem créditos disponíveis!');</script>";
            }
        } else {
            $dados = $_POST;

            $sql = "UPDATE sessoes SET";
            $sql .= " data_agendada = '{$dados['data_agendada']}',";
            $sql .= " hora_agendada = '{$dados['hora_agendada']}',";
            $sql .= " status = '{$dados['status']}'";
            $sql .= " WHERE id = '{$dados['id']}'";

            if (mysqli_query($conn, $sql)) {
                print "<script>alert('Sessão alterada com sucesso!');
                            window.location.href = 'index.php';
                            </script>";
            } else {
                print "<script>alert('Erro ao salvar sessão: " . mysqli_error($conn) . "');</script>";
            }
        }
    } elseif ($_REQUEST['action'] == 'edit') {
        $id = $_GET['id'];

        $sql = "SELECT * FROM sessoes WHERE id = '{$id}'";
        $result = mysqli_query($conn, $sql);

        if ($row = mysqli_fetch_assoc($result)) {
            $dados['id'] = $row['id'];
            $dados['paciente_id'] = $row['paciente_id'];
            $dados['psicologo_id'] = $row['psicologo_id'];
            $dados['venda_origem_id'] = $row['venda_origem_id'];
            $dados['data_agendada'] = $row['data_agendada'];
            $dados['hora_agendada'] = $row['hora_agendada'];
            $dados['status'] = $row['status'];
        }
    }
}
?>

<div class="w3-container w3-padding  w3-margin-bottom">
    <div class="w3-container w3-padding  w3-margin-bottom">
        <h3>Formulário de Sessão</h3>
    </div>
    <div class="w3-card w3-white w3-padding w3-margin-top w3-round" style="max-width:750px; margin:auto;">
        <form enctype="multipart/form-data" method="post" action="sessao_form.php?action=save">

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
                        <option value="AGENDADA" <?= ($dados['status'] == 'AGENDADA') ? 'selected' : '' ?>>Agendada</option>
                        <option value="REALIZADA" <?= ($dados['status'] == 'REALIZADA') ? 'selected' : '' ?>>Realizada</option>
                        <option value="REMARCADA" <?= ($dados['status'] == 'REMARCADA') ? 'selected' : '' ?>>Remarcada</option>
                        <option value="CANCELADA_PACIENTE" <?= ($dados['status'] == 'CANCELADA_PACIENTE') ? 'selected' : '' ?>>Paciente Cancelou</option>
                        <option value="CANCELADA_PSICOLOGO" <?= ($dados['status'] == 'CANCELADA_PSICOLOGO') ? 'selected' : '' ?>>Psicólogo Cancelou</option>
                    </select>
                </div>
            </div>

            <!-- Paciente -->
            <div class="w3-row-padding w3-margin-top">
                <div class="w3-col">
                    <label>Paciente</label>
                    <select name="paciente_id" class="w3-select w3-border w3-round" required>
                        <option value="">Selecione...</option>

                        <?php
                        $sql = "SELECT id, nome_completo FROM pacientes WHERE ativo = '1'";
                        $result = mysqli_query($conn, $sql);
                        if ($result) {
                            while ($row = mysqli_fetch_assoc($result)) {
                                $selected = ($row['id'] == $dados['paciente_id']) ? 'selected' : '';
                                print "<option value='{$row['id']}' $selected>{$row['nome_completo']}</option>";
                            }
                        }
                        ?>
                    </select>
                </div>
            </div>

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

            <!-- Data e Hora de Inicio -->
            <div class="w3-row-padding w3-margin-top">
                <div class="w3-half">
                    <label>Data da Sessão</label>
                    <input name="data_agendada" type="date" value="<?= $dados['data_agendada'] ?>" class="w3-input w3-border w3-round" required>
                </div>
                <div class="w3-half">
                    <label>Hora da Sessão</label>
                    <input name="hora_agendada" type="time" value="<?= $dados['hora_agendada'] ?>" class="w3-input w3-border w3-round" required>
                </div>
            </div>

            <!-- Botões -->
            <div class="w3-row-padding w3-margin-top">
                <div class="w3-col">
                    <button type="submit" class="w3-button w3-green w3-round w3-margin-bottom w3-margin-top w3-margin-right">
                        <span class="material-symbols-outlined">save</span>
                        <b>Salvar</b>
                    </button>
                    <button type="reset" class="w3-button w3-red w3-round w3-margin-bottom w3-margin-top w3-margin-right">
                        <span class="material-symbols-outlined">cancel</span>
                        <b>Cancelar</b>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php
mysqli_close($conn);
include_once __DIR__ . '/../../includes/footer.php';
?>