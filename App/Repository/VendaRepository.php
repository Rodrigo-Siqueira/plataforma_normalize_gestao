<?php

declare(strict_types=1);

namespace App\Repository;

use App\Enum\TipoVendaStatus;
use App\Model\VendaModel;
use Exception;
use Normalize\Database\Conexao;
use Normalize\Database\Transacao;

class VendaRepository
{
    public function salvar(VendaModel $venda): int
    {
        $pdo = Transacao::pegarConexaoAtiva();

        $sql = "INSERT INTO venda (
            paciente_id,
            responsavel_pessoa_id,
            produto_id,
            valor_cobrado,
            forma_pagamento,
            status_pagamento,
            data_venda,
            data_pagamento) 
        VALUES (:pacienteId, :responsavelPessoaId, :produtoId, :valorCobrado, :formaPagamento, :statusPagamento, :dataVenda, :dataPagamento)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':pacienteId', $venda->getPacienteId(), \PDO::PARAM_INT);
        $stmt->bindValue(':responsavelPessoaId', $venda->getResponsavelId(), \PDO::PARAM_INT);
        $stmt->bindValue(':produtoId', $venda->getProdutoId(), \PDO::PARAM_INT);
        $stmt->bindValue(':valorCobrado', $venda->getValorCobrado());
        $stmt->bindValue(':formaPagamento', $venda->getFormaPagamento()->value);
        $stmt->bindValue(':statusPagamento', $venda->getStatus()->value);
        $stmt->bindValue(':dataVenda', $venda->getDataVenda()?->format('Y-m-d'));
        $stmt->bindValue(':dataPagamento', $venda->getDataPagamento()?->format('Y-m-d H:i:s'));

        if (!$stmt->execute()) {
            throw new Exception("Falha ao inserir a venda no banco de dados.");
        }

        return (int) $pdo->lastInsertId();
    }

    public function alterar(VendaModel $venda): bool
    {
        $pdo = Transacao::pegarConexaoAtiva();

        $sql = "UPDATE venda SET
            paciente_id = :pacienteId, 
            responsavel_pessoa_id = :responsavelPessoaId, 
            produto_id = :produtoId, 
            valor_cobrado = :valorCobrado, 
            status_pagamento = :statusPagamento, 
            forma_pagamento = :formaPagamento, 
            data_venda = :dataVenda,
            data_pagamento = :dataPagamento
        WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':pacienteId', $venda->getPacienteId(), \PDO::PARAM_INT);
        $stmt->bindValue(':responsavelPessoaId', $venda->getResponsavelId(), \PDO::PARAM_INT);
        $stmt->bindValue(':produtoId', $venda->getProdutoId(), \PDO::PARAM_INT);
        $stmt->bindValue(':valorCobrado', $venda->getValorCobrado());
        $stmt->bindValue(':formaPagamento', $venda->getFormaPagamento()->value);
        $stmt->bindValue(':statusPagamento', $venda->getStatus()->value);
        $stmt->bindValue(':dataVenda', $venda->getDataVenda()?->format('Y-m-d'));
        $stmt->bindValue(':dataPagamento', $venda->getDataPagamento()?->format('Y-m-d H:i:s'));
        $stmt->bindValue(':id', $venda->getId(), \PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function listarPaginado(int $limite, int $deslocamento): array
    {
        try {
            $sql = "SELECT
                v.id,
                pePac.nome_completo AS pacienteNome,
                p.nome AS produtoNome,
                v.valor_cobrado AS valorCobrado,
                v.data_venda AS dataVenda,
                v.status_pagamento AS statusPagamento,
                v.data_pagamento AS dataPagamento
            FROM venda AS v
            JOIN paciente AS pa 
                ON pa.id = v.paciente_id
            JOIN pessoa AS pePac 
                ON pePac.id = pa.pessoa_id
            JOIN produto AS p 
                ON p.id = v.produto_id
            ORDER BY v.id DESC LIMIT :limite OFFSET :deslocamento";

            $stmt = Conexao::abrirConexao()->prepare($sql);
            $stmt->bindValue(':limite', $limite, \PDO::PARAM_INT);
            $stmt->bindValue(':deslocamento', $deslocamento, \PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll();
        } catch (Exception $e) {
            throw new Exception('Houve um erro ao listar as vendas' . $e->getMessage());
        }
    }

    public function contarTotal(?TipoVendaStatus $status = null): int
    {
        $sql = "SELECT COUNT(*) FROM venda";

        if (!is_null($status)) {
            $sql .= " WHERE status_pagamento = :status";
        }

        $stmt = Conexao::abrirConexao()->prepare($sql);

        if (!is_null($status)) {
            $stmt->bindValue(':status', $status->value);
        }

        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }

    public function buscarVendaId(int $id): ?object
    {
        try {
            $sql = "SELECT 
                id,
                paciente_id,
                responsavel_pessoa_id,
                produto_id,
                valor_cobrado,
                forma_pagamento,
                status_pagamento,
                data_venda,
                data_pagamento
            FROM venda WHERE id = :id";

            $stmt = Conexao::abrirConexao()->prepare($sql);
            $stmt->bindValue(':id', $id, \PDO::PARAM_INT);
            $stmt->execute();

            $resultado = $stmt->fetch(\PDO::FETCH_OBJ);

            return $resultado ?: null;
        } catch (Exception $e) {
            throw new Exception('Houve um erro ao buscar a venda ' . $e->getMessage());
        }
    }

    public function somarCreditosPagosPorPaciente(int $pacienteId): int
    {
        $sql = "SELECT 
            SUM(p.numero_sessoes) AS total_creditos
        FROM venda v
        JOIN produto p ON v.produto_id = p.id
        WHERE v.paciente_id = :id 
        AND v.status_pagamento = 'PAGO'";

        $stmt = Conexao::abrirConexao()->prepare($sql);
        $stmt->bindValue(':id', $pacienteId, \PDO::PARAM_INT);
        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }

    public function buscarIdProximaVendaDisponivel(int $pacienteId): ?int
    {
        $sql = " SELECT 
            v.id
        FROM venda v
            JOIN produto p ON v.produto_id = p.id
        LEFT JOIN sessao s ON s.venda_origem_id = v.id
        WHERE v.paciente_id = :id AND v.status_pagamento = 'PAGO'
        GROUP BY v.id, p.numero_sessoes
        HAVING COUNT(s.id) < p.numero_sessoes
        ORDER BY v.data_venda ASC LIMIT 1";

        $stmt = Conexao::abrirConexao()->prepare($sql);
        $stmt->bindValue(':id', $pacienteId, \PDO::PARAM_INT);
        $stmt->execute();
        $resultado = $stmt->fetchColumn();

        return $resultado !== false ? (int) $resultado : null;
    }
}
