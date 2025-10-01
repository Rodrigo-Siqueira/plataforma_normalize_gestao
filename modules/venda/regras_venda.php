<?php
require_once '../db/config.php';

# Transforma erros em exceções
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

function listar_vendas() {
    try {
        $conn = criar_conexao_db();

        $sql = "SELECT
            v.id,
            pa.nome_completo,
            p.nome,
            v.valor_cobrado,
            DATE_FORMAT(v.data_venda, '%d/%m/%Y') AS data_venda,
            v.status_pagamento,
            DATE_FORMAT(v.data_pagamento, '%d/%m/%Y') AS data_pagamento
        FROM venda AS v
        JOIN paciente AS pa ON pa.id = v.paciente_id
        JOIN produto AS p ON p.id = v.produto_id
        ORDER BY v.id";

        $resultado = mysqli_query($conn, $sql);
        $vendas = mysqli_fetch_all($resultado, MYSQLI_ASSOC);

        return $vendas;
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

function adicionar_venda($dados) {
    try {
        $conn = criar_conexao_db();
        mysqli_begin_transaction($conn);

        if (empty($dados['data_pagamento'])) {
            $sql = "INSERT INTO venda (
                paciente_id,
                responsavel_id,
                produto_id,
                valor_cobrado,
                forma_pagamento,
                status_pagamento) 
            VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param(
                $stmt, 'iiidss',
                $dados['paciente_id'],
                $dados['responsavel_id'],
                $dados['produto_id'],
                $dados['preco'],
                $dados['forma_pagamento'],
                $dados['status']
            );

        } else {
            $sql = "INSERT INTO venda (
                paciente_id,
                responsavel_id,
                produto_id,
                valor_cobrado,
                forma_pagamento,
                status_pagamento,
                data_pagamento) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param(
                $stmt,
                'iiidsss',
                $dados['paciente_id'],
                $dados['responsavel_id'],
                $dados['produto_id'],
                $dados['preco'],
                $dados['forma_pagamento'],
                $dados['status'],
                $dados['data_pagamento']
            );
        }
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        mysqli_commit($conn);

        return [
            'sucesso' => true,
            'mensagem' => 'Venda adicionada com sucesso!'
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

function buscar_venda_id($id) {
    try {
        $conn = criar_conexao_db();

        $sql = "SELECT * FROM venda WHERE id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);

        $resultado = mysqli_stmt_get_result($stmt);

        if ($venda = mysqli_fetch_assoc($resultado)) {
            return $venda;
        } else {
            return [
                'sucesso' => false,
                'mensagem' => 'Venda não encontrado!'
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

function alterar_venda($dados) {
    try {
        $conn = criar_conexao_db();
        mysqli_begin_transaction($conn);

        $sql = "UPDATE venda SET
            paciente_id = ?, 
            responsavel_id = ?, 
            produto_id = ?, 
            valor_cobrado = ?, 
            status_pagamento = ?, 
            forma_pagamento = ?, 
            data_pagamento = ?
        WHERE id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param(
            $stmt, 'iiidsssi',
            $dados['paciente_id'],
            $dados['responsavel_id'],
            $dados['produto_id'],
            $dados['preco'],
            $dados['status'],
            $dados['forma_pagamento'],
            $dados['data_pagamento'],
            $dados['id']
        );
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        mysqli_commit($conn);
        return [
            'sucesso' => true,
            'mensagem' => 'Venda alterada com sucesso!'
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