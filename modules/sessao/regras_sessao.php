<?php
include_once '../db/config.php';
# Transforma erros em exceções
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

function listar_sessoes() {
    try {
        $conn = criar_conexao_db();

        $sql = "SELECT * FROM 
            (SELECT 
                s.id, 
                p.nome_completo AS paciente,
                psi.nome_completo AS psicologo,
                DATE_FORMAT(s.data_agendada, '%d/%m/%Y') AS data_agendada, 
                TIME_FORMAT(s.hora_agendada, '%H:%i') AS hora_agendada, 
                s.status,
                CONCAT(
                    ROW_NUMBER() OVER (
                        PARTITION BY s.venda_origem_id 
                        ORDER BY s.data_agendada
                    ),
                    '/',
                    prod.numero_sessoes
                ) AS carimbo
                FROM sessao AS s
                JOIN paciente AS p ON p.id = s.paciente_id
                JOIN psicologo AS psi ON psi.id = s.psicologo_id
                JOIN venda v ON v.id = s.venda_origem_id
                JOIN produto prod ON prod.id = v.produto_id
            ) AS todas
        WHERE todas.status = 'AGENDADA'
        ORDER BY todas.data_agendada";
        $resultado  = mysqli_query($conn, $sql);
        $sessoes = mysqli_fetch_all($resultado, MYSQLI_ASSOC);

        return $sessoes;

    } catch (mysqli_sql_exception $e) {
        return [
            'sucesso' => false,
            'mensagem' => $e->getMessage()
        ];
    } finally {
        if (isset($conn) && $conn) {
            mysqli_close($conn);
        }
    }
}

function credito_sessoes_paga($dados) {
    try {
        $conn = criar_conexao_db();

        $sql = "SELECT 
            SUM(p.numero_sessoes) AS total_creditos
        FROM venda v
        JOIN produto p ON v.produto_id = p.id
        WHERE v.paciente_id = ? 
        AND v.status_pagamento = 'PAGO'";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, 'i', $dados['paciente_id']);
        mysqli_stmt_execute($stmt);

        $resultado = mysqli_stmt_get_result($stmt);
        $sql_creditos = mysqli_fetch_assoc($resultado);
        $creditos = intval($sql_creditos['total_creditos'] ?? 0);

        return $creditos;

    } catch (mysqli_sql_exception $e) {
        if (isset($stmt) && $stmt) {
            mysqli_stmt_close($stmt);
        }

        return [
            'sucesso' => false,
            'mensagem' => $e->getMessage()
        ];

    } finally {
        if (isset($conn) && $conn) {
            mysqli_close($conn);
        }
    }
}

function debito_sessoes_registradas($dados) {
    try {
        $conn = criar_conexao_db();

        $sql = "SELECT 
            COUNT(id) AS total_debitos
        FROM sessao
        WHERE paciente_id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, 'i', $dados['paciente_id']);
        mysqli_stmt_execute($stmt);

        $resultado = mysqli_stmt_get_result($stmt);
        $sql_debitos = mysqli_fetch_assoc($resultado);
        $debitos = intval($sql_debitos['total_debitos'] ?? 0);

        return $debitos;

    } catch (mysqli_sql_exception $e) {
        if (isset($stmt) && $stmt) {
            mysqli_stmt_close($stmt);
        }

        return [
            'sucesso' => false,
            'mensagem' => $e->getMessage()
        ];
    } finally {
        if (isset($conn) && $conn) {
            mysqli_close($conn);
        }
    }
}

function buscar_proxima_venda_disponivel($dados) {
    try {
        $conn = criar_conexao_db();

        $sql = " SELECT 
            v.id
        FROM venda v
        JOIN produto p ON v.produto_id = p.id
        LEFT JOIN sessao s ON s.venda_origem_id = v.id
        WHERE v.paciente_id = ? AND v.status_pagamento = 'PAGO'
        GROUP BY v.id, p.numero_sessoes
        HAVING COUNT(s.id) < p.numero_sessoes
        ORDER BY v.data_venda ASC
        LIMIT 1";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, 'i', $dados['paciente_id']);
        mysqli_stmt_execute($stmt);

        $resultado = mysqli_stmt_get_result($stmt);
        $venda_disponivel = mysqli_fetch_assoc($resultado);

        return $venda_disponivel;
    } catch (mysqli_sql_exception $e) {
        if (isset($stmt) && $stmt) {
            mysqli_stmt_close($stmt);
        }

        return [
            'sucesso' => false,
            'mensagem' => $e->getMessage()
        ];
    } finally {
        if (isset($conn) && $conn) {
            mysqli_close($conn);
        }
    }
}

function adicionar_sessao($dados) {
    try {
        $conn = criar_conexao_db();
        mysqli_begin_transaction($conn);

        $sql = "INSERT INTO 
            sessao (
                paciente_id,
                psicologo_id,
                venda_origem_id,
                data_agendada,
                hora_agendada,
                status)
        VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param(
            $stmt, 'iiisss',
            $dados['paciente_id'],
            $dados['psicologo_id'],
            $dados['venda_origem_id'],
            $dados['data_agendada'],
            $dados['hora_agendada'],
            $dados['status']
        );
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        mysqli_commit($conn);
        return [
            'sucesso' => true,
            'mensagem' => 'Sessão adicionada com sucesso!'
        ];
    } catch (mysqli_sql_exception $e) {
        if (isset($conn) && $conn) {

            mysqli_rollback($conn);
        }
    } finally {
        if (isset($conn) && $conn) {
            mysqli_close($conn);
        }
    }
}

function buscar_sessao_por_id($id) {
    try {
        $conn = criar_conexao_db();

        $sql = "SELECT * FROM sessao WHERE id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);

        $resultado = mysqli_stmt_get_result($stmt);

        if ($sessao = mysqli_fetch_assoc($resultado)) {
            return $sessao;
        } else {
            return [
                'sucesso' => false,
                'mensagem' => 'Sessão não encontrado!'
            ];
        }
    } catch (mysqli_sql_exception $e) {
        return [
            'sucesso' => false,
            'mensagem' => $e->getMessage()
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

function alterar_sessao($dados) {
    try {
        $conn = criar_conexao_db();
        mysqli_begin_transaction($conn);

        $sql = "UPDATE sessao SET
            data_agendada = ?, 
            hora_agendada = ?, 
            status = ? 
        WHERE id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param(
            $stmt,
            'sssi',
            $dados['data_agendada'],
            $dados['hora_agendada'],
            $dados['status'],
            $dados['id']
        );
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        mysqli_commit($conn);
        return [
            'sucesso' => true,
            'mensagem' => 'Sessão alterada com sucesso!'
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