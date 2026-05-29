<?php

namespace App\Repository;

use Exception;
use Normalize\Database\Conexao;

class FluxoCaixaRepository
{
    public function listarEntradasPago(): array
    {
        try {
            $sql = "SELECT
                DATE_FORMAT(v.data_pagamento, '%Y-%m-%d') as data,
                p.nome as descricao,
                pe.nome_completo as detalhe,
                v.valor_cobrado as valor,
                'VENDA' as tipo
            FROM venda v
            JOIN produto p ON v.produto_id = p.id
            JOIN paciente pac ON v.paciente_id = pac.id
            JOIN pessoa pe ON pe.id = pac.pessoa_id
            WHERE v.status_pagamento = 'PAGO'
            ORDER BY v.data_venda DESC";

            $conn = Conexao::abrirConexao();
            $stmt = $conn->prepare($sql);
            $stmt->execute();

            return $stmt->fetchAll();
        } catch (Exception $e) {
            throw new Exception('Erro ao retornar entradas pago' . $e->getMessage());
        }
    }

    public function listarDespesasPago(): array
    {
        try {
            $sql = "SELECT
                DATE_FORMAT(desp.data_pagamento, '%Y-%m-%d') as data,
                desp.descricao as descricao,
                desp.categoria as detalhe,
                desp.valor as valor,
                'DESPESA' as tipo
            FROM despesa_operacional AS desp
            WHERE desp.status = 'PAGO'
            ORDER BY desp.data_pagamento DESC";

            $conn = Conexao::abrirConexao();
            $stmt = $conn->prepare($sql);
            $stmt->execute();

            return $stmt->fetchAll();
        } catch (Exception $e) {
            throw new Exception('Erro ao retornar despesas pago' . $e->getMessage());
        }
    }

    public function listarRepassesPago(): array
    {
        try {
            $sql = "SELECT 
                DATE_FORMAT(l.data_pagamento, '%Y-%m-%d') as data,
                pe.nome_completo as descricao,
                CONCAT(
                    DATE_FORMAT(l.periodo_inicio, '%d'),
                    ' à ',
                    DATE_FORMAT(l.periodo_fim, '%d/%m/%Y')
                ) as detalhe,
                l.valor_liquido_pago as valor,
                'REPASSE' as tipo
            FROM lote_repasse AS l 
            JOIN psicologo p ON p.id = l.psicologo_id
            JOIN pessoa pe ON pe.id = p.pessoa_id
            WHERE l.status = 'PAGO'
            ORDER BY l.data_pagamento DESC";

            $conn = Conexao::abrirConexao();
            $stmt = $conn->prepare($sql);
            $stmt->execute();

            return $stmt->fetchAll();
        } catch (Exception $e) {
            throw new Exception('Erro ao retornar repasses pago' . $e->getMessage());
        }
    }
}
