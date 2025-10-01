<?php
require_once '../db/config.php';

# Transforma erros em exceções
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

function listar_pacientes() {
    try {
        $conn = criar_conexao_db();

        $sql = "SELECT
            p.id, 
            p.nome_completo AS paciente, 
            p.contato, 
            r.id AS id_resp, 
            r.nome_completo AS responsavel,
            psi.nome_completo AS psicologo
        FROM paciente AS p
        INNER JOIN responsavel_financeiro AS r 
            ON p.responsavel_financeiro_id = r.id
        INNER JOIN psicologo AS psi
            ON p.psicologo_responsavel_id = psi.id
        WHERE p.ativo = 1
        ORDER BY id";

        $resultado = mysqli_query($conn, $sql);
        $pacientes = mysqli_fetch_all($resultado, MYSQLI_ASSOC);

        return $pacientes;
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

function buscar_paciente_id($id) {
    try {
        $conn = criar_conexao_db();

        $sql = "SELECT * FROM paciente WHERE id= ?";

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

function adicionar_paciente($dados) {
    try {
        $conn = criar_conexao_db();
        mysqli_begin_transaction($conn);

        $sql = "INSERT INTO paciente (
            responsavel_financeiro_id,
            psicologo_responsavel_id,
            nome_completo,
            contato,
            ativo)
        VALUES (?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param(
            $stmt, 'iissi',
            $dados['responsavel_id'],
            $dados['psicologo_id'],
            $dados['nome'],
            $dados['contato'],
            $dados['ativo']
        );
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        mysqli_commit($conn);

        return [
            'sucesso' => true,
            'mensagem' => 'Pacienten adicionado com sucesso'
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

function alterar_paciente($dados) {
    try {
        $conn = criar_conexao_db();
        mysqli_begin_transaction($conn);

        $sql = "UPDATE paciente SET
            responsavel_financeiro_id = ?, 
            psicologo_responsavel_id = ?, 
            nome_completo = ?, 
            contato = ?, 
            ativo = ? 
        WHERE id = ?";

        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param(
            $stmt, 'iissii',
            $dados['responsavel_id'],
            $dados['psicologo_id'],
            $dados['nome'],
            $dados['contato'],
            $dados['ativo'],
            $dados['id']
        );
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        mysqli_commit($conn);

        return [
            'sucesso' => true,
            'mensagem' => 'Paciente alterado com sucesso'
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