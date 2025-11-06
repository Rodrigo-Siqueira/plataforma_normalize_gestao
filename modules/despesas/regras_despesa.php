<?php
require_once '../db/config.php';

# Transforma erros em exceções
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

function listar_despesas() {
    try {
        $conn = criar_conexao_db();

        $sql = "SELECT 
            id,
            descricao,
            tipo, 
            categoria, 
            valor, 
            DATE_FORMAT(data_competencia, '%d/%m/%Y') AS data_competencia,
            DATE_FORMAT(data_pagamento, '%d/%m/%Y') AS data_pagamento,
            status
         FROM despesa_operacional ORDER BY id";

        $resultado = mysqli_query($conn, $sql);
        $despesas = mysqli_fetch_all($resultado, MYSQLI_ASSOC);

        return $despesas;
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

function adicionar_despesa($dados) {
    try {
        $conn = criar_conexao_db();
        mysqli_begin_transaction($conn);

        $sql = "INSERT INTO despesa_operacional (
            descricao,
            tipo, 
            categoria, 
            valor, 
            data_competencia, 
            status) 
        VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param(
            $stmt, 'sssdss',
            $dados['descricao'],
            $dados['tipo'],
            $dados['categoria'],
            $dados['valor'],        
            $dados['data_competencia'],
            $dados['status']
        );

        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        mysqli_commit($conn);

        return [
            'sucesso' => true,
            'mensagem' => 'Despesa adicionada com sucesso'
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

function buscar_despesa_id($id) {
    try {
        $conn = criar_conexao_db();

        $sql = "SELECT * FROM despesa_operacional WHERE id= ?";

        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);

        $resultado = mysqli_stmt_get_result($stmt);

        if ($despesa = mysqli_fetch_assoc($resultado)) {
            return $despesa;
        } else {
            return [
                'sucesso' => false,
                'mensagem' => 'Despesa não encontrada!'
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

        # Fechar a conexão com o banco
        if (isset($conn) && $conn) {
            mysqli_close($conn);
        }
    }
}

function alterar_despesa($dados) {
    try {
        $conn = criar_conexao_db();
        mysqli_begin_transaction($conn);

        $sql = "UPDATE despesa_operacional SET
            descricao = ?,
            tipo = ?, 
            categoria = ?, 
            valor = ?, 
            data_competencia = ?,
            data_pagamento = ?, 
            status = ?
        WHERE id = ?";

        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param(
            $stmt, 'sssdsssi',
            $dados['descricao'],
            $dados['tipo'],
            $dados['categoria'],
            $dados['valor'],
            $dados['data_competencia'],
            $dados['data_pagamento'],
            $dados['status'],
            $dados['id']
        );
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        mysqli_commit($conn);

        return [
            'sucesso' => true,
            'mensagem' => 'Despesa alterada com sucesso'
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