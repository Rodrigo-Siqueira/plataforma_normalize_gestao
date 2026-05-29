<?php

// Habilitar o modo estrito para tipos
declare(strict_types=1);

// Importar o log
require_once ROOT_PATH . '/modules/log/regra_log.php';

// Importar arquivo para conexão com DB 
require_once ROOT_PATH . '/modules/db/config.php';

// Transformar erros em exceções
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

# FEITO
function criarPessoa(mysqli $conn, string $nomeCompleto, string $contato): int
{
    $stmt = null;
    try {
        $sql = "INSERT INTO pessoa (nome_completo, contato) VALUES (?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, 'ss', $nomeCompleto, $contato);
        mysqli_stmt_execute($stmt);
        $pessoaId = mysqli_insert_id($conn);

        return (int)$pessoaId;
    } catch (\Throwable $e) {
        logErro('Erro ao retornar ID da pessoa.', $e);
        throw new RuntimeException('Desculpe ocorreu um erro inesperado ao completar a tarefa.', 0, $e);
    } finally {
        if ($stmt) {
            mysqli_stmt_close($stmt);
        }
    }
}

function buscarPessoaPorNomeContato(mysqli $conn, string $nomeCompleto, string $contato): ?int
{
    $stmt = null;
    try {
        $sql = "SELECT id FROM pessoa WHERE nome_completo = ? AND contato = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, 'ss', $nomeCompleto, $contato);
        mysqli_stmt_execute($stmt);
        $resultado = mysqli_stmt_get_result($stmt);
        $pessoa = mysqli_fetch_assoc($resultado);

        return $pessoa ? (int)$pessoa['id'] : null;
    } catch (\Throwable $e) {
        logErro('Erro ao buscar pessoa por nome e contato.', $e);
        throw new RuntimeException('Desculpe ocorreu um erro inesperado ao completar a tarefa.', 0, $e);
    } finally {
        if ($stmt) {
            mysqli_stmt_close($stmt);
        }
    }
}

function buscarOuCriarPessoa(mysqli $conn, string $nomeCompleto, string $contato): int
{
    $pessoaId = buscarPessoaPorNomeContato($conn, $nomeCompleto, $contato);
    // Verica se $pessoa_id é diferente de null
    if ($pessoaId !== null) {
        return $pessoaId;
    } else {
        return criarPessoa($conn, $nomeCompleto, $contato);
    }
}

function atualizarPessoa(mysqli $conn, int $pessoaId, string $nomeCompleto, string $contato): bool
{
    $stmt = null;
    try {
        $sql = "UPDATE pessoa SET nome_completo = ?, contato = ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, 'ssi', $nomeCompleto, $contato, $pessoaId);
        mysqli_stmt_execute($stmt);

        return mysqli_stmt_affected_rows($stmt) > 0;
    } catch (\Throwable $e) {
        logErro('Erro ao atualizar pessoa.', $e);
        throw new RuntimeException('Desculpe ocorreu um erro inesperado ao atualizar pessoa.', 0, $e);
    } finally {
        if ($stmt) {
            mysqli_stmt_close($stmt);
        }
    }
}

function buscarPessoaPorId(mysqli $conn, int $pessoaId): ?array
{
    $stmt = null;
    try {
        $sql = "SELECT id, nome_completo, contato FROM pessoa WHERE id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, 'i', $pessoaId);
        mysqli_stmt_execute($stmt);
        $resultado = mysqli_stmt_get_result($stmt);
        $pessoa = mysqli_fetch_assoc($resultado);

        return $pessoa ?: null;
    } catch (\Throwable $e) {
        logErro('Erro ao buscar pessoa por ID.', $e);
        throw new RuntimeException('Desculpe ocorreu um erro inesperado ao completar a tarefa.', 0, $e);
    } finally {
        if ($stmt) {
            mysqli_stmt_close($stmt);
        }
    }
}
