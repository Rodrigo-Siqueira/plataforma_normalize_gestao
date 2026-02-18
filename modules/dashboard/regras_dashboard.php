<?php
require_once __DIR__ . '/../db/config.php';

# Transforma erros em exceções
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

/**
 * Funções de consulta (simples, retornam arrays)
 * Todas usam tabelas no singular: venda, sessao, paciente, psicologo, lote_repasse, despesa_operacional, produto
 */

function faturamento_total_mes()
{
    $conn = criar_conexao_db();
    $sql = "SELECT COALESCE(SUM(valor_cobrado),0) AS total
            FROM venda
            WHERE status_pagamento = 'PAGO'
              AND YEAR(data_pagamento) = YEAR(CURDATE())
              AND MONTH(data_pagamento) = MONTH(CURDATE())";
    $res = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($res);
    mysqli_close($conn);
    return (float) ($row['total'] ?? 0);
}

function contar_pacientes_ativos()
{
    $conn = criar_conexao_db();
    $sql = "SELECT COUNT(*) AS total FROM paciente WHERE ativo = 1";
    $res = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($res);
    mysqli_close($conn);
    return (int) ($row['total'] ?? 0);
}

function contar_repasses_pendentes()
{
    $conn = criar_conexao_db();
    $sql = "SELECT COUNT(*) AS total FROM lote_repasse WHERE status = 'ABERTO' OR status = 'PENDENTE'";
    $res = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($res);
    mysqli_close($conn);
    return (int) ($row['total'] ?? 0);
}

function sessoes_realizadas_hoje()
{
    $conn = criar_conexao_db();
    $sql = "SELECT COUNT(*) AS total FROM sessao WHERE status = 'REALIZADA' AND data_agendada = CURDATE()";
    $res = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($res);
    mysqli_close($conn);
    return (int) ($row['total'] ?? 0);
}

function listar_sessoes_proximas($dias = 1)
{
    $conn = criar_conexao_db();
    $sql = "SELECT s.id, p.nome_completo AS paciente, psi.nome_completo AS psicologo, s.data_agendada, s.hora_agendada, s.status
            FROM sessao s
            JOIN paciente p ON p.id = s.paciente_id
            JOIN psicologo psi ON psi.id = s.psicologo_id
            WHERE s.data_agendada BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL ? DAY)
            ORDER BY s.data_agendada ASC, s.hora_agendada ASC";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $dias);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $rows = mysqli_fetch_all($res, MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
    mysqli_close($conn);
    return $rows;
}

/**
 * Pacientes para renovar (configurável limite restante)
 * Retorna vendas pagas com sessoes_restantes <= $limite e paciente ativo
 */
function listar_pacientes_para_renovar($limite_sessoes = 1)
{
    try {
        $conn = criar_conexao_db();
        $sql = "SELECT 
                    v.id AS venda_id,
                    p.id AS paciente_id,
                    p.nome_completo AS paciente,
                    psi.nome_completo AS psicologo,
                    prod.nome AS produto,
                    prod.numero_sessoes,
                    COALESCE(COUNT(s.id), 0) AS sessoes_realizadas,
                    (prod.numero_sessoes - COALESCE(COUNT(s.id), 0)) AS sessoes_restantes,
                    MAX(s.data_agendada) AS ultima_sessao
                FROM venda v
                JOIN paciente p ON p.id = v.paciente_id
                JOIN psicologo psi ON psi.id = p.psicologo_responsavel_id
                JOIN produto prod ON prod.id = v.produto_id
            LEFT JOIN sessao s 
                ON s.venda_origem_id = v.id 
                AND s.status = 'REALIZADA'

            WHERE 
                v.status_pagamento = 'PAGO'
                AND p.ativo = 1

                -- pega somente a última venda paga do paciente
                AND v.id = (
                    SELECT id 
                    FROM venda 
                    WHERE paciente_id = p.id 
                    AND status_pagamento = 'PAGO'
                    ORDER BY data_pagamento DESC 
                    LIMIT 1
                )
            GROUP BY v.id
            HAVING sessoes_restantes <= ?  
            ORDER BY ultima_sessao ASC;";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $limite_sessoes);
        mysqli_stmt_execute($stmt);

        $resultado = mysqli_stmt_get_result($stmt);
        $lista = mysqli_fetch_all($resultado, MYSQLI_ASSOC);

        return $lista;
    } catch (mysqli_sql_exception $e) {
        return [
            'sucesso' => false,
            'erro' => $e->getMessage()
        ];
    } finally {
        if (isset($conn) && $conn) mysqli_close($conn);
    }
}

/**
 * Sessões por psicólogo (mês atual) — para gráfico/bar chart
 */
function sessoes_por_psicologo_mes()
{
    $conn = criar_conexao_db();
    $sql = "SELECT psi.nome_completo AS psicologo, COUNT(*) AS total
            FROM sessao s
            JOIN psicologo psi ON psi.id = s.psicologo_id
            WHERE s.status = 'REALIZADA'
              AND YEAR(s.data_agendada) = YEAR(CURDATE())
              AND MONTH(s.data_agendada) = MONTH(CURDATE())
            GROUP BY psi.id
            ORDER BY total DESC";
    $res = mysqli_query($conn, $sql);
    $rows = mysqli_fetch_all($res, MYSQLI_ASSOC);
    mysqli_close($conn);
    return $rows;
}

/**
 * Dados para gráfico financeiro (últimos 6 meses)
 */
function fluxo_financeiro_ultimos_meses($meses = 6)
{
    $conn = criar_conexao_db();
    $sql = "SELECT month_label, 
                   SUM(total_vendas) AS total_vendas,
                   SUM(total_despesas) AS total_despesas,
                   SUM(total_repasses) AS total_repasses
            FROM (
                SELECT DATE_FORMAT(v.data_pagamento, '%Y-%m') AS month_label, v.valor_cobrado AS total_vendas, 0 AS total_despesas, 0 AS total_repasses
                FROM venda v
                WHERE v.status_pagamento = 'PAGO'
                UNION ALL
                SELECT DATE_FORMAT(d.data_pagamento, '%Y-%m') AS month_label, 0 AS total_vendas, d.valor AS total_despesas, 0 AS total_repasses
                FROM despesa_operacional d
                WHERE d.status = 'PAGO'
                UNION ALL
                SELECT DATE_FORMAT(l.data_pagamento, '%Y-%m') AS month_label, 0 AS total_vendas, 0 AS total_despesas, l.valor_liquido_pago AS total_repasses
                FROM lote_repasse l
                WHERE l.status = 'PAGO'
            ) AS union_all
            GROUP BY month_label
            ORDER BY month_label DESC
            LIMIT ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $meses);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $rows = mysqli_fetch_all($res, MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
    mysqli_close($conn);
    return array_reverse($rows); // volta para ordem cronológica asc para gráfico
}

/* Funções utilitárias para exibir */
function fmt_money($v)
{
    return 'R$ ' . number_format((float)$v, 2, ',', '.');
}
