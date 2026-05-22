<?php
require_once '../db/config.php';

// Transforma erros em exceções
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

function adicionar_psicologo($dados) {
    try {
        $conn = criar_conexao_db();
        mysqli_begin_transaction($conn);

        // Adicionar psicologo em pessoa primeiro
        $sqlPessoa = "INSERT INTO pessoa (
            nome_completo,
            contato)
        VALUES (?, ?)";

        $stmt = mysqli_prepare($conn, $sqlPessoa);
        mysqli_stmt_bind_param(
            $stmt, 'ss',
            $dados['nome'],
            $dados['contato']
        );
        mysqli_stmt_execute($stmt);
        // Pega o ID de pessoa para depois adicionar em psicologo
        $pessoaID = mysqli_insert_id($conn);
        mysqli_stmt_close($stmt);

        // Verfica se $pessoaID pegou o ID
        if (!$pessoaID) {
            throw new Exception("Erro ao obter ID da pessoa.");
        }

        $sql = "INSERT INTO psicologo (
                pessoa_id,
                crp,
                percentual_comissao,
                data_inicio,
                ativo)
            VALUES (?, ?, ?, ?, ?)";

        $stmt = mysqli_prepare($conn, $sql);

        mysqli_stmt_bind_param(
            $stmt,
            'isdsi',
            $pessoaID,
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

    } catch (mysqli_sql_exception $e) {
        // Se aconteceu algum erro retornar a transação
        if (isset($conn) && $conn) {
            mysqli_rollback($conn);
        }
        // Capturar e retornar a exceção em caso de erro        
        return [
            'sucesso' => false,
            'erro' => $e->getMessage()
        ];
    } finally {
        // Fechar a conexão com o banco
        if (isset($conn) && $conn) {
            mysqli_close($conn);
        }
    }
}

function listar_psicologo() {
    try {
        $conn =  criar_conexao_db();
        // Select lista todos os psicolos cadastrados
        $sql = "SELECT
            psi.id,
            pe.nome_completo,
            pe.contato,
            psi.crp,
            psi.ativo
            FROM psicologo AS psi
        JOIN pessoa AS pe
        ON psi.pessoa_id = pe.id";

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

        // Seleciona todos os campos de psicologo
        // e nome_completo e contato de pessoas
        $sql = "SELECT
            psi.id,
            psi.pessoa_id,
            pe.nome_completo,
            pe.contato,
            psi.crp,
            psi.percentual_comissao,
            psi.data_inicio,
            psi.ativo
            FROM psicologo AS psi
        JOIN pessoa AS pe
        ON psi.pessoa_id = pe.id
        WHERE psi.id = ?";

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
        // Capturar e retornar a exceção em caso de erro
        return [
            'sucesso' => false,
            'erro' => $e->getMessage()
        ];
    } finally {
        // Fechar a declaração
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
        // Criar conexão com banco de dados
        $conn = criar_conexao_db();
        mysqli_begin_transaction($conn);

        $sqlPessoa = "SELECT 
            nome_completo,
            contato
        FROM pessoa
        WHERE id = ?";
        $stmtPessoa = mysqli_prepare($conn, $sqlPessoa);
        mysqli_stmt_bind_param(
            $stmtPessoa, 'i',
            $dados['pessoa_id']
        );
        mysqli_stmt_execute($stmtPessoa);
        $resultado = mysqli_stmt_get_result($stmtPessoa);
        $pessoa = mysqli_fetch_assoc($resultado);
        mysqli_stmt_close($stmtPessoa);

        if (!$pessoa) {
            throw new Exception("Pessoa não encontrada.");
        }

        // Verifica os dados são diferentes da tabela pessoa
        if ($dados['nome'] !== $pessoa['nome_completo'] 
            || $dados['contato'] !== $pessoa['contato']) {
            // Atualiza os dados da tabela pessoa
            $sql = "UPDATE pessoa SET 
                nome_completo = ?, 
                contato = ?
            WHERE id = ?";

            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param(
                $stmt,
                'ssi',
                $dados['nome'],
                $dados['contato'],
                $dados['pessoa_id']
            );
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }

        // Atualiza os dados da tabela psicologo
        $sql = "UPDATE psicologo SET 
            crp = ?,
            percentual_comissao = ?, 
            ativo = ?
        WHERE id = ?";

        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param(
            $stmt, 'sdii',
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
        // Se aconteceu algum erro retornar a transação
        if (isset($conn) && $conn) {
            mysqli_rollback($conn);
        }

        // Capturar e retornar a exceção em caso de erro        
        return [
            'sucesso' => false,
            'erro' => $e->getMessage()
        ];
    } finally {
        // Fechar a conexão com o banco
        if (isset($conn) && $conn) {
            mysqli_close($conn);
        }
    }
}