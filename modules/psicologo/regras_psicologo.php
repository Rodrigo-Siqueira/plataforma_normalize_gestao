<?php
require_once '../db/config.php';

# Transforma erros em exceções
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

function adicionar_psicologo($dados) {
    try {
        # Criar conexão com banco de dados
        $conn = criar_conexao_db();
        mysqli_begin_transaction($conn);

        $sql = "INSERT INTO psicologo (
            nome_completo,
            contato,
            crp,
            percentual_comissao,
            data_inicio,
            ativo)
        VALUES (?, ?, ?, ?, ?, ?)";

        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param(
            $stmt, 'sssdsi',
            $dados['nome'],
            $dados['contato'],
            $dados['crp'],
            $dados['comissao'],
            $dados['data_inicio'],
            $dados['ativo']
        );

        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        mysqli_commit($conn);

        return [
            'sucesso' => true,
            'mensagem' => 'Psicologo adicionado com sucesso!'
        ];

    } catch (\mysqli_sql_exception $e) {
        # Se aconteceu algum erro retornar a transação
        if (isset($conn) && $conn) {
            mysqli_rollback($conn);
        }
        # Capturar e retornar a exceção em caso de erro        
        return [
            'successo' => false,
            'erro' => $e->getMessage()
        ];
    } finally {
        # Fechar a conexão com o banco
        if (isset($conn) && $conn) {
            mysqli_close($conn);
        }
    }
}

function listar_psicologo() {
    try {
        $conn =  criar_conexao_db();

        $sql = "SELECT * FROM psicologo WHERE ativo = 1";

        $resultado = mysqli_query($conn, $sql);

        $psicologos = mysqli_fetch_all($resultado, MYSQLI_ASSOC);
        mysqli_free_result($resultado);

        return $psicologos;

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

function buscar_psicologo_id($id) {
    try {
        $conn = criar_conexao_db();

        $sql = "SELECT * FROM psicologo WHERE id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, 'i', $id);

        mysqli_stmt_execute($stmt);

        $resultado = mysqli_stmt_get_result($stmt);

        if ($psicologo = mysqli_fetch_assoc($resultado)) {
            return $psicologo;
        } else {
            return [
                'sucesso' => false,
                'mensagem' => 'Psicologo não encontrado!'
            ];
        }

    } catch (mysqli_sql_exception $e) {
        # Capturar e retornar a exceção em caso de erro
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

function alterar_psicologo($dados) {
    
    try {
        # Criar conexão com banco de dados
        $conn = criar_conexao_db();
        mysqli_begin_transaction($conn);

        $sql = "UPDATE psicologo SET 
            nome_completo = ?, 
            contato = ?,
            crp = ?,
            percentual_comissao = ?, 
            ativo = ?
        WHERE id = ?";

        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param(
            $stmt, 'sssdii',
            $dados['nome'],
            $dados['contato'],
            $dados['crp'],
            $dados['comissao'],
            $dados['ativo'],
            $dados['id']
        );
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        mysqli_commit($conn);

        return [
            'sucesso' => true,
            'mensagem' => 'Psicologo alterado com sucesso!'
        ];
    } catch (mysqli_sql_exception $e) {
        # Se aconteceu algum erro retornar a transação
        if (isset($conn) && $conn) {
            mysqli_rollback($conn);
        }

        # Capturar e retornar a exceção em caso de erro        
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