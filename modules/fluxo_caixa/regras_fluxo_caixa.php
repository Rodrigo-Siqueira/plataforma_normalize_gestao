<?php
require_once '../db/config.php';

# Transforma erros em exceções
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

function listar_entradas_pago() {
    try {
        $conn = criar_conexao_db();

        $sql = "SELECT
            DATE_FORMAT(v.data_pagamento, '%Y-%m-%d') as data,
            p.nome as descricao,
            pac.nome_completo as detalhe,
            v.valor_cobrado as valor,
            'VENDA' as tipo
        FROM venda v
        JOIN produto p ON v.produto_id = p.id
        JOIN paciente pac ON v.paciente_id = pac.id
        WHERE v.status_pagamento = 'PAGO'
        ORDER BY v.data_venda DESC";

        $resultado = mysqli_query($conn, $sql);
        return mysqli_fetch_all($resultado, MYSQLI_ASSOC);
    } catch (mysqli_sql_exception $e) {
        return ['sucesso' => false, 'erro' => $e->getMessage()];
    } finally {
        if (isset($conn)) mysqli_close($conn);
    }
}

function listar_despesas_pago() {
    try {
        $conn = criar_conexao_db();

        $sql = "SELECT
            DATE_FORMAT(desp.data_pagamento, '%Y-%m-%d') as data,
            desp.descricao as descricao,
            desp.categoria as detalhe,
            desp.valor as valor,
            'DESPESA' as tipo
        FROM despesa_operacional AS desp
        WHERE desp.status = 'PAGO'
        ORDER BY desp.data_pagamento DESC";

        $resultado = mysqli_query($conn, $sql);
        return mysqli_fetch_all($resultado, MYSQLI_ASSOC);
    } catch (mysqli_sql_exception $e) {
        return ['sucesso' => false, 'erro' => $e->getMessage()];
    } finally {
        if (isset($conn)) mysqli_close($conn);
    }
}

function listar_repasses_pago() {
    try {
        $conn = criar_conexao_db();

        $sql = "SELECT 
            DATE_FORMAT(l.data_pagamento, '%Y-%m-%d') as data,
            p.nome_completo as descricao,
            CONCAT(
                DATE_FORMAT(l.periodo_inicio, '%d'),
                ' à ',
                DATE_FORMAT(l.periodo_fim, '%d/%m/%Y')
            ) as detalhe,
            l.valor_liquido_pago as valor,
            'REPASSE' as tipo
        FROM lote_repasse AS l 
        JOIN psicologo p ON p.id = l.psicologo_id
        WHERE l.status = 'PAGO'
        ORDER BY l.data_pagamento DESC";

        $resultado = mysqli_query($conn, $sql);
        return mysqli_fetch_all($resultado, MYSQLI_ASSOC);
    } catch (mysqli_sql_exception $e) {
        return ['sucesso' => false, 'erro' => $e->getMessage()];
    } finally {
        if (isset($conn)) mysqli_close($conn);
    }
}
