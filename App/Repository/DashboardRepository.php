<?php

namespace App\Repository;

use Exception;
use Normalize\Database\Conexao;

class DashboardRepository
{
    public function faturamentoTotalMes(): float
    {
        try {
            $sql = "SELECT COALESCE(SUM(valor_cobrado),0) AS total
                FROM venda
            WHERE status_pagamento = 'PAGO'
                AND YEAR(data_pagamento) = YEAR(CURDATE())
                AND MONTH(data_pagamento) = MONTH(CURDATE())";

            $conn = Conexao::abrirConexao();
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            
            return (float) $stmt->fetchColumn();
        } catch (Exception $e) {
            throw new Exception('Erro ao calcular faturamento: ' . $e->getMessage());
        } finally {
            $conn = null;
        }
    }

    public function contarPacientesAtivos(): int
    {
        try {
            $sql = "SELECT COUNT(*) AS total FROM paciente WHERE ativo = 1";

            $conn = Conexao::abrirConexao();
            $stmt = $conn->prepare($sql);
            $stmt->execute();

            return (int) $stmt->fetchColumn();
        } catch (Exception $e) {
            throw new Exception('Erro ao calcular pacientes ativos: ' . $e->getMessage());
        } finally {
            $conn = null;
        }
    }

    public function contarRepassesPendentes(): int
    {
        try {
            $sql = "SELECT COUNT(*) AS total FROM lote_repasse 
            WHERE status = 'ABERTO' OR status = 'PENDENTE'";

            $conn = Conexao::abrirConexao();
            $stmt = $conn->prepare($sql);
            $stmt->execute();

            return (int) $stmt->fetchColumn();
        } catch (Exception $e) {
            throw new Exception('Erro ao calcular repassses pendentes: ' . $e->getMessage());
        } finally {
            $conn = null;
        }
    }

    public function sessoesRealizadasHoje(): int
    {
        try {
            $sql = "SELECT COUNT(*) AS total FROM sessao 
            WHERE status = 'REALIZADA' AND data_agendada = CURDATE()";

            $conn = Conexao::abrirConexao();
            $stmt = $conn->prepare($sql);
            $stmt->execute();

            return (int) $stmt->fetchColumn();
        } catch (Exception $e) {
            throw new Exception('Erro ao calcular as sessoes que foram realizadas hoje: ' . $e->getMessage());
        } finally {
            $conn = null;
        }
    }

    public function listarSessoesProximas(int $dias = 1): array
    {
        try {
            $sql = "SELECT 
                s.id, 
                pePac.nome_completo AS paciente, 
                pePsi.nome_completo AS psicologo, 
                s.data_agendada, 
                s.hora_agendada, 
                s.status
            FROM sessao s
                JOIN paciente pa ON pa.id = s.paciente_id
                JOIN pessoa pePac ON pePac.id = pa.pessoa_id
                JOIN psicologo psi ON psi.id = s.psicologo_id
                JOIN pessoa pePsi ON pePsi.id = psi.pessoa_id
            WHERE s.data_agendada BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL :dias DAY)
            ORDER BY s.data_agendada ASC, s.hora_agendada ASC";

            $conn = Conexao::abrirConexao();
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':dias', $dias);
            $stmt->execute();

            return $stmt->fetchAll();
        } catch (Exception $e) {
            throw new Exception('Erro ao listar as próximas sessoes: ' . $e->getMessage());
        } finally {
            $conn = null;
        }
    }

    public function listarPacientesParaRenovar(int $limiteSessoes = 1): array
    {
        try {
            $sql = "SELECT 
                v.id AS venda_id,
                p.id AS paciente_id,
                pePac.nome_completo AS paciente,
                psi.nome_completo AS psicologo,
                prod.nome AS produto,
                prod.numero_sessoes,
                COALESCE(COUNT(s.id), 0) AS sessoesRealizadas,
                (prod.numero_sessoes - COALESCE(COUNT(s.id), 0)) AS sessoesRestantes,
                MAX(s.data_agendada) AS ultimaSessao
            FROM venda v
            JOIN paciente p ON p.id = v.paciente_id
            JOIN pessoa pePac ON pePac.id = p.pessoa_id
            JOIN psicologo ps ON ps.id = p.psicologo_responsavel_id
            JOIN pessoa psi ON psi.id = ps.pessoa_id
            JOIN produto prod ON prod.id = v.produto_id
            LEFT JOIN sessao s 
                ON s.venda_origem_id = v.id 
                AND s.status = 'REALIZADA'
            WHERE 
                v.status_pagamento = 'PAGO'
                AND p.ativo = 1
                AND v.id = (
                    SELECT id 
                    FROM venda 
                    WHERE paciente_id = p.id 
                    AND status_pagamento = 'PAGO'
                    ORDER BY data_pagamento DESC 
                    LIMIT 1
                )
            GROUP BY v.id
            HAVING sessoesRestantes <= :limiteSessoes  
            ORDER BY ultimaSessao ASC";

            $conn = Conexao::abrirConexao();
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':limiteSessoes', $limiteSessoes);
            $stmt->execute();

            return $stmt->fetchAll();
        } catch (Exception $e) {
            throw new Exception('Erro ao listar pacientes para renovação: ' . $e->getMessage());
        } finally {
            $conn = null;
        }
    }

    public function sessoesPorPsicologoMes(): array
    {
        try {
            $sql = "SELECT psi.nome_completo AS psicologo, COUNT(*) AS total
            FROM sessao s
            JOIN pessoa psi ON psi.id = s.psicologo_id
            WHERE s.status = 'REALIZADA'
              AND YEAR(s.data_agendada) = YEAR(CURDATE())
              AND MONTH(s.data_agendada) = MONTH(CURDATE())
            GROUP BY psi.id
            ORDER BY total DESC";

            $conn = Conexao::abrirConexao();
            $stmt = $conn->prepare($sql);
            $stmt->execute();

            return $stmt->fetchAll();
        } catch (Exception $e) {
            throw new Exception('Erro ao listar sessões por psicólogos: ' . $e->getMessage());
        } finally {
            $conn = null;
        }    
    }

    public function fluxoFinancUltimosSeisMeses(int $meses = 6): array
    {
        try {
            $sql = "SELECT meses, 
                        SUM(totalVendas) AS totalVendas,
                        SUM(totalDespesas) AS totalDespesas,
                        SUM(totalRepasses) AS totalRepasses
                    FROM (
                        SELECT DATE_FORMAT(v.data_pagamento, '%Y-%m') AS meses, v.valor_cobrado AS totalVendas, 0 AS totalDespesas, 0 AS totalRepasses
                        FROM venda v
                        WHERE v.status_pagamento = 'PAGO'
                    UNION ALL
                        SELECT DATE_FORMAT(d.data_pagamento, '%Y-%m') AS meses, 0 AS totalVendas, d.valor AS totalDespesas, 0 AS totalRepasses
                        FROM despesa_operacional d
                        WHERE d.status = 'PAGO'
                    UNION ALL
                        SELECT DATE_FORMAT(l.data_pagamento, '%Y-%m') AS meses, 0 AS totalVendas, 0 AS totalDespesas, l.valor_liquido_pago AS totalRepasses
                        FROM lote_repasse l
                        WHERE l.status = 'PAGO') AS union_all
            GROUP BY meses
            ORDER BY meses DESC
            LIMIT :meses";
            
            $conn = Conexao::abrirConexao();
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':meses', $meses, \PDO::PARAM_INT);
            $stmt->execute();

            return array_reverse($stmt->fetchAll());
        } catch (Exception $e) {
            throw new Exception('Erro ao gerar fluxo financeiro: ' . $e->getMessage());
        } finally {
            $conn = null;
        }
    }
}