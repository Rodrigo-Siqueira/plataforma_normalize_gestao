<?php
include_once '../db/config.php';
# Transforma erros em exceções
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// FEITO
function listar_repasses() {
    try {
        $conn = criar_conexao_db();

        $sql = "SELECT 
	        l.id,
	        pe.nome_completo,
            DATE_FORMAT(l.periodo_inicio, '%d/%m/%Y') AS periodo_inicio,
            DATE_FORMAT(l.periodo_fim, '%d/%m/%Y') AS periodo_fim,
            l.valor_liquido_pago,
            l.status
        FROM lote_repasse AS l 
        JOIN psicologo p ON p.id = l.psicologo_id
        JOIN pessoa pe ON pe.id = p.pessoa_id
        ORDER BY l.id";
        $resultado  = mysqli_query($conn, $sql);
        $repasses = mysqli_fetch_all($resultado, MYSQLI_ASSOC);

        return $repasses;
    } catch (mysqli_sql_exception $e) {
        return [
            'sucesso' => false,
            'erro' => $e->getMessage()
        ];
    } finally {
        if (isset($conn) && $conn) {
            mysqli_close($conn);
        }
    }
}

// FEITO
function listar_sessoes_vinculada_repasse($id)
{
    try {
        $conn = criar_conexao_db();

        $sql = "SELECT 
            s.id, 
            pePac.nome_completo AS paciente,
            pePsi.nome_completo AS psico, 
            DATE_FORMAT(s.data_agendada, '%d/%m/%Y') AS data_agendada,
            TIME_FORMAT(s.hora_agendada, '%H:%i') AS hora_agendada,
            ((v.valor_cobrado / prod.numero_sessoes) * psi.percentual_comissao) AS valor_repasse,
            s.status
        FROM sessao AS s
        JOIN paciente p ON p.id = s.paciente_id
        JOIN pessoa pePac ON pePac.id = p.pessoa_id
        JOIN psicologo psi ON psi.id = s.psicologo_id
        JOIN pessoa pePsi ON pePsi.id = psi.pessoa_id
        JOIN venda v ON s.venda_origem_id = v.id
        JOIN produto prod ON prod.id = v.produto_id
        WHERE s.lote_repasse_id = ?
        ORDER BY s.id";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);

        $resultado = mysqli_stmt_get_result($stmt);
        $sessoes_repasse = mysqli_fetch_all($resultado, MYSQLI_ASSOC);
        mysqli_stmt_close($stmt);

        return $sessoes_repasse;
    } catch (mysqli_sql_exception $e) {

        return [
            'sucesso' => false,
            'erro' => $e->getMessage()
        ];
    } finally {
        if (isset($conn) && $conn) {
            mysqli_close($conn);
        }
    }
}

// FEITO
function buscar_repasse_por_id($id)
{
    try {
        $conn = criar_conexao_db();

        $sql = "SELECT * FROM lote_repasse WHERE id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);

        $resultado = mysqli_stmt_get_result($stmt);

        if ($repasse = mysqli_fetch_assoc($resultado)) {
            return $repasse;
        } else {
            return [
                'sucesso' => false,
                'mensagem' => 'Repasse não encontrado!'
            ];
        }
    } catch (mysqli_sql_exception $e) {
        return [
            'sucesso' => false,
            'erro' => $e->getMessage()
        ];
    } finally {
        # Fechar a declaração
        if (isset($stmt) && $stmt) {
            mysqli_stmt_close($stmt);
        }

        if (isset($conn) && $conn) {
            mysqli_close($conn);
        }
    }
}

// FEITO
function gerar_repasse($dados, $sessoes)
{
    try {
        $valor_total_repasse = 0;
        $ids_sessoes_processadas = [];

        $conn = criar_conexao_db();
        mysqli_begin_transaction($conn);

        foreach ($sessoes as $sessao) {
            $valor_total_repasse += ($sessao['valor_cobrado'] / $sessao['numero_sessoes']) * $sessao['percentual_comissao'];
            $ids_sessoes_processadas[] = $sessao['sessao_id'];
        }

        $sql = "INSERT INTO 
            lote_repasse (
                psicologo_id, 
                periodo_inicio, 
                periodo_fim, 
                valor_liquido_pago) 
        VALUES (?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param(
            $stmt,
            'issd',
            $dados['psicologo_id'],
            $dados['periodo_inicio'],
            $dados['periodo_fim'],
            $valor_total_repasse
        );
        mysqli_stmt_execute($stmt);

        $lote_id = mysqli_insert_id($conn);
        if (!$lote_id) {
            mysqli_rollback($conn);
            return [
                'sucesso' => false,
                'mensagem' => 'Erro ao criar lote de repasse.'
            ];
        } else {
            $ids = implode(",", $ids_sessoes_processadas);
            $sql = "UPDATE sessao SET lote_repasse_id = ? WHERE id IN ($ids)";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, 'i', $lote_id);
            mysqli_stmt_execute($stmt);

            mysqli_commit($conn);
            return [
                'sucesso' => true,
                'mensagem' => 'Lote de repasse criado e sessões atualizadas com sucesso!'
            ];
        }
    } catch (mysqli_sql_exception $e) {
        if (isset($conn) && $conn) {

            mysqli_rollback($conn);
        }

        return [
            'sucesso' => false,
            'erro' => $e->getMessage()
        ];
    } finally {
        if (isset($conn) && $conn) {
            mysqli_close($conn);
        }
    }
}

// FEITO
function alterar_repasse($dados)
{
    try {
        $conn = criar_conexao_db();
        mysqli_begin_transaction($conn);

        $data_pagamento = ($dados['status'] === 'PAGO') ? date('Y-m-d H:i:s') : null;

        $sql = "UPDATE lote_repasse SET 
            status = ?, 
            data_pagamento = ?
        WHERE id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param(
            $stmt,
            'ssi',
            $dados['status'],
            $data_pagamento,
            $dados['id']
        );
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        mysqli_commit($conn);
        return [
            'sucesso' => true,
            'mensagem' => 'Lote alterado com sucesso'
        ];
    } catch (mysqli_sql_exception $e) {
        if (isset($conn) && $conn) {

            mysqli_rollback($conn);
        }

        return [
            'sucesso' => false,
            'erro' => $e->getMessage()
        ];
    } finally {
        if (isset($conn) && $conn) {
            mysqli_close($conn);
        }
    }
}

// FEITO
function listar_sessoes_disp_repasse()
{
    try {
        $conn = criar_conexao_db();

        $sql = "SELECT 
            s.id, 
            pePAC.nome_completo AS paciente,
            pePsi.nome_completo, 
            DATE_FORMAT(s.data_agendada, '%d/%m/%Y') AS data_agendada, 
            TIME_FORMAT(s.hora_agendada, '%H:%i') AS hora_agendada, 
            s.status
        FROM sessao AS s
        JOIN paciente AS p ON p.id = s.paciente_id
        JOIN pessoa AS pePAC ON pePAC.id = p.pessoa_id
        JOIN psicologo AS psi ON psi.id = s.psicologo_id
        JOIN pessoa AS pePsi ON pePsi.id = psi.pessoa_id
        WHERE s.status = 'REALIZADA'
        AND s.lote_repasse_id IS NULL
        ORDER BY s.id";
        $resultado  = mysqli_query($conn, $sql);
        $sessoes_repasse = mysqli_fetch_all($resultado, MYSQLI_ASSOC);

        return $sessoes_repasse;
    } catch (mysqli_sql_exception $e) {
        return [
            'sucesso' => false,
            'erro' => $e->getMessage()
        ];
    } finally {
        if (isset($conn) && $conn) {
            mysqli_close($conn);
        }
    }
}

// FEITO
function listar_sessoes_mes($dados)
{
    try {
        $conn = criar_conexao_db();

        $sql = "SELECT 
            s.id as sessao_id,
            v.valor_cobrado,
            prod.numero_sessoes,
            p.percentual_comissao
        FROM sessao s
        JOIN venda v ON s.venda_origem_id = v.id
        JOIN psicologo p ON s.psicologo_id = p.id
        JOIN produto prod ON prod.id = v.produto_id
        WHERE s.psicologo_id = ?
        AND s.status = 'REALIZADA'
        AND s.lote_repasse_id IS NULL
        AND s.data_agendada BETWEEN ? AND ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param(
            $stmt,
            'iss',
            $dados['psicologo_id'],
            $dados['periodo_inicio'],
            $dados['periodo_fim']
        );
        mysqli_stmt_execute($stmt);

        $resultado = mysqli_stmt_get_result($stmt);
        $sessoes_mes = mysqli_fetch_all($resultado, MYSQLI_ASSOC);
        mysqli_stmt_close($stmt);

        return $sessoes_mes;
    } catch (mysqli_sql_exception $e) {

        return [
            'sucesso' => false,
            'erro' => $e->getMessage()
        ];
    } finally {
        if (isset($conn) && $conn) {
            mysqli_close($conn);
        }
    }
}