<?php

declare(strict_types=1);

namespace App\Repository;

use App\Dto\Output\DespesaOutputDTO;
use App\Enum\TipoStatusDespesa;
use App\Model\DespesaModel;
use Exception;
use Normalize\Database\Conexao;

class DespesaRepository
{
    public function salvar(DespesaModel $despesa): int
    {
        try {
            $sql = "INSERT INTO despesa_operacional (
                descricao,
                tipo, 
                categoria, 
                valor, 
                data_competencia,
                data_pagamento, 
                status) 
            VALUES (:descricao, :tipo, :categoria, :valor, :dataCompetencia, :dataPagamento, :status)";
            $pdo = Conexao::abrirConexao();
            $stmt = $pdo->prepare($sql);

            $stmt->bindValue(':descricao', $despesa->getDescricao());
            $stmt->bindValue(':tipo', $despesa->getTipo()->value);
            $stmt->bindValue(':categoria', $despesa->getCategoria()->value);
            $stmt->bindValue(':valor', $despesa->getValor());
            $stmt->bindValue(':dataCompetencia', $despesa->getDataDespesa()->format('Y-m-d'));
            $stmt->bindValue(':dataPagamento', $despesa->getDataPagamento()?->format('Y-m-d'));
            $stmt->bindValue(':status', $despesa->getStatus()->value);

            if (!$stmt->execute()) {
                throw new Exception("Falha ao inserir a despesa no banco de dados.");
            }

            return (int) $pdo->lastInsertId();

        } catch (Exception $e) {
            throw new Exception('Houve um erro ao salvar a despesa: ' . $e->getMessage());
        }
    }

    public function alterar(DespesaModel $despesa): bool
    {
        if ($despesa->getId() === null) {
            throw new Exception("Não é possível alterar uma despesa sem ID.");
        }

        try {
            $sql = "UPDATE despesa_operacional SET
                descricao = :descricao,
                tipo = :tipo, 
                categoria = :categoria, 
                valor = :valor, 
                data_competencia = :dataCompetencia,
                data_pagamento = :dataPagamento, 
                status = :status
            WHERE id = :id";
            $pdo = Conexao::abrirConexao();
            $stmt = $pdo->prepare($sql);

            $stmt->bindValue(':descricao', $despesa->getDescricao());
            $stmt->bindValue(':tipo', $despesa->getTipo()->value);
            $stmt->bindValue(':categoria', $despesa->getCategoria()->value);
            $stmt->bindValue(':valor', $despesa->getValor());
            $stmt->bindValue(':dataCompetencia', $despesa->getDataDespesa()->format('Y-m-d'));
            $stmt->bindValue(':dataPagamento', $despesa->getDataPagamento()?->format('Y-m-d'));
            $stmt->bindValue(':status', $despesa->getStatus()->value);
            $stmt->bindValue(':id', $despesa->getId());

            if (!$stmt->execute()) {
                throw new Exception("Falha ao alterar a despesa no banco de dados.");
            }

            return true;

        } catch (Exception $e) {
            throw new Exception('Houve um erro ao alterar a despesa: ' . $e->getMessage());
        }
    }

    public function  listarPaginando(int $limite, int $deslocamento): array
    {
        try {
            $sql = "SELECT 
                id,
                descricao,
                tipo, 
                categoria, 
                valor, 
                data_competencia,
                data_pagamento,
                status
            FROM despesa_operacional
            ORDER BY id DESC LIMIT :limite OFFSET :deslocamento";
            $stmt = Conexao::abrirConexao()->prepare($sql);
            $stmt->bindValue(':limite', $limite, \PDO::PARAM_INT);
            $stmt->bindValue(':deslocamento', $deslocamento, \PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll();
        } catch (Exception $e) {
            throw new Exception('Houve um erro ao listar as despesa: ' . $e->getMessage());
        }
    }

    public function contarTotal(?TipoStatusDespesa $status = null): int
    {
        $sql = "SELECT COUNT(*) FROM despesa_operacional";

        if (!is_null($status)) {
            $sql .= " WHERE status = :status";
        }

        $stmt = Conexao::abrirConexao()->prepare($sql);

        if (!is_null($status)) {
            $stmt->bindValue(':status', $status->value);
        }

        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }

    public function buscarPorId(int $id): ?object
    {
        try {
            $sql = "SELECT * FROM despesa_operacional WHERE id= :id";
            $stmt = Conexao::abrirConexao()->prepare($sql);
            $stmt->execute([':id' => $id]);

            $resultado = $stmt->fetch();
            return $resultado ?: null;
        } catch (Exception $e) {
            throw new Exception('Houve um erro ao buscar a despesa: ' . $e->getMessage());
        }
    }
}