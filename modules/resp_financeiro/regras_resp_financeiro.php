<?php
require_once '../db/config.php';

# Transforma erros em exceções
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

function listar_resp_financeiros() {
    try {
        $conn = criar_conexao_db();

        $sql = "SELECT * FROM responsavel_financeiro ORDER BY id";
        $resultado = mysqli_query($conn, $sql);
        $responsaveis_finan = mysqli_fetch_all($resultado, MYSQLI_ASSOC);

        return $responsaveis_finan;

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

function adicionar_resp_financeiro($dados) {
    try {
        $conn = criar_conexao_db();
        mysqli_begin_transaction($conn);

        $sql = "INSERT INTO responsavel_financeiro(
            nome_completo,
            contato,
            eh_paciente)
        VALUES (?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param(
            $stmt, 'ssi',
            $dados['nome'],
            $dados['contato'],
            $dados['eh_paciente']
        );
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        mysqli_commit($conn);

        return [
            'sucesso' => true,
            'mensagem' => 'Responsavel Financeiro adicionado com sucesso!'
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
        # Fechar a conexão com o banco
        if (isset($conn) && $conn) {
            mysqli_close($conn);
        }
    }
}

function buscar_resp_financeiro_id($id) {
    try {
        $conn = criar_conexao_db();

        $sql = "SELECT * FROM responsavel_financeiro WHERE id= ?";

        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);

        $resultado = mysqli_stmt_get_result($stmt);

        if ($responsavel_finan = mysqli_fetch_assoc($resultado)) {
            return $responsavel_finan;
        } else {
            return [
                'sucesso' => false,
                'mensagem' => 'Responsavel Financeiro não encontrado!'
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

function alterar_resp_financeiro($dados) {
    try {
        $conn = criar_conexao_db();
        mysqli_begin_transaction($conn);

        $sql = "UPDATE responsavel_financeiro SET
            nome_completo = ?, 
            contato = ?
        WHERE id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, 'ssi', 
            $dados['nome'], 
            $dados['contato'],
            $dados['id']
        );
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        mysqli_commit($conn);
        return [
            'sucesso' => true,
            'mensagem' => 'Responsavel Financeiro alterado com sucesso!'
        ];
    } catch (mysqli_sql_exception $e) {
        if (isset($conn) && $conn) {
            mysqli_rollback($conn);
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