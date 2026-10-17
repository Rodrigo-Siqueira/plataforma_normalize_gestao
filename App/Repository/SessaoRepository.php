<?php

declare(strict_types=1);

namespace App\Repository;

use App\Model\SessaoModel;
use DateTimeImmutable;
use Exception;
use Normalize\Database\Conexao;
use Normalize\Database\Transacao;

class SessaoRepository
{
    public function salvar(SessaoModel $sessao): int
    {
        $pdo = Transacao::pegarConexaoAtiva();

        $sql = "INSERT INTO sessao (
            paciente_id,
            psicologo_id,
            venda_origem_id,
            data_agendada,
            hora_agendada,
            status)
        VALUES (:pacienteId, :psicologoId, :vendaOrigemId, :dataAgendada, :horaAgendada, :statusSessao)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':pacienteId', $sessao->getPacienteId(), \PDO::PARAM_INT);
        $stmt->bindValue(':psicologoId', $sessao->getPsicologoId(), \PDO::PARAM_INT);
        $stmt->bindValue(':vendaOrigemId', $sessao->getVendaOrigemId(), \PDO::PARAM_INT);
        $stmt->bindValue(':dataAgendada', $sessao->getDataAgendada()->format('Y-m-d'));
        $stmt->bindValue(':horaAgendada', $sessao->getHoraAgendada()->format('H:i:s'));
        $stmt->bindValue(':statusSessao', $sessao->getStatusSessao()->value);

        if (!$stmt->execute()) {
            throw new Exception("Falha ao inserir a Sessão no banco de dados.");
        }

        return (int) $pdo->lastInsertId();
    }

    public function alterar(SessaoModel $sessao): bool
    {
        $pdo = Transacao::pegarConexaoAtiva();

        $sql = "UPDATE sessao SET
            psicologo_id =  :psicologoId,
            data_agendada = :dataAgendada, 
            hora_agendada = :horaAgendada, 
            status = :statusSessao 
        WHERE id = :id";

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':psicologoId', $sessao->getPsicologoId(), \PDO::PARAM_INT);
        $stmt->bindValue(':dataAgendada', $sessao->getDataAgendada()->format('Y-m-d'));
        $stmt->bindValue(':horaAgendada', $sessao->getHoraAgendada()->format('H:i:s'));
        $stmt->bindValue(':statusSessao', $sessao->getStatusSessao()->value);
        $stmt->bindValue(':id', $sessao->getId(), \PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function contarSessoesPorPaciente(int $id): int
    {
        $sql = "SELECT COUNT(id) AS total_debitos FROM sessao WHERE paciente_id = :id";
        $stmt = Conexao::abrirConexao()->prepare($sql);
        $stmt->bindValue(':id', $id, \PDO::PARAM_INT);
        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }

    public function listarPaginado(int $limite, int $deslocamento): array
    {
        try {
            $sql = "WITH SessoesNumeradas AS (
                SELECT 
                    id, 
                    paciente_id,
                    psicologo_id,
                    venda_origem_id,
                    data_agendada, 
                    hora_agendada, 
                    status,
                    ROW_NUMBER() OVER (
                        PARTITION BY venda_origem_id 
                        ORDER BY data_agendada ASC, hora_agendada ASC
                    ) AS indice_sessao
                FROM sessao
            )
            SELECT 
                sn.id,
                sn.paciente_id,
                sn.psicologo_id,
                sn.venda_origem_id,
                pa.nome_completo AS paciente,
                pe.nome_completo AS psicologo,
                sn.data_agendada, 
                sn.hora_agendada, 
                sn.status,
                CONCAT(sn.indice_sessao, '/', prod.numero_sessoes) AS carimbo
            FROM SessoesNumeradas sn
            JOIN paciente p ON p.id = sn.paciente_id
            JOIN pessoa pa ON pa.id = p.pessoa_id
            JOIN psicologo psi ON psi.id = sn.psicologo_id
            JOIN pessoa pe ON pe.id = psi.pessoa_id
            JOIN venda v ON v.id = sn.venda_origem_id
            JOIN produto prod ON prod.id = v.produto_id
            WHERE sn.status IN ('AGENDADA', 'REMARCADA')
            ORDER BY sn.data_agendada ASC, sn.hora_agendada ASC
            LIMIT :limite OFFSET :deslocamento";

            $stmt = Conexao::abrirConexao()->prepare($sql);
            $stmt->bindValue(':limite', $limite, \PDO::PARAM_INT);
            $stmt->bindValue(':deslocamento', $deslocamento, \PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll();
        } catch (Exception $e) {
            throw new Exception('Houve um erro ao listar as sessões.' . $e->getMessage());
        }
    }

    public function contarTotal(): int
    {
        $sql = "SELECT COUNT(*) FROM sessao WHERE status IN ('AGENDADA', 'REMARCADA')";
        $stmt = Conexao::abrirConexao()->prepare($sql);
        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }

    public function buscarSessaoPorId(int $id): ?object
    {
        try {
            $sql = "SELECT
                id,
                paciente_id,
                psicologo_id,
                venda_origem_id,
                lote_repasse_id,
                data_agendada,
                hora_agendada,
                status
            FROM sessao WHERE id = :id";

            $stmt = Conexao::abrirConexao()->prepare($sql);
            $stmt->bindValue(':id', $id, \PDO::PARAM_INT);
            $stmt->execute();

            $resultado = $stmt->fetch();

            return $resultado ?: null;
        } catch (Exception $e) {
            throw new Exception('Houve um erro ao buscar a sessão ' . $e->getMessage());
        }
    }

    public function atualizarStatus(SessaoModel $sessao): bool
    {
        $sql = "UPDATE sessao SET status = :statusSessao  WHERE id = :id";
        $stmt = Conexao::abrirConexao()->prepare($sql);
        $stmt->bindValue(':statusSessao', $sessao->getStatusSessao()->value);
        $stmt->bindValue(':id', $sessao->getId(), \PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function buscarSessoesSeguintesDaVenda(
        int $vendaOrigemId,
        DateTimeImmutable $dataReferenciaOriginal,
        DateTimeImmutable $horaReferenciaOriginal
    ): array {
        $sql = "SELECT
            id,
            paciente_id,
            psicologo_id,
            venda_origem_id,
            lote_repasse_id,
            data_agendada,
            hora_agendada,
            status
        FROM sessao
        WHERE venda_origem_id = :vendaId
        AND status IN ('AGENDADA', 'REMARCADA')
        AND (
            data_agendada > :dataReferencia
            OR (
                data_agendada = :dataReferencia
                AND hora_agendada > :horaReferencia
            )
        )
        ORDER BY data_agendada ASC, hora_agendada ASC";

        $stmt = Conexao::abrirConexao()->prepare($sql);

        $stmt->bindValue(
            ':vendaId',
            $vendaOrigemId,
            \PDO::PARAM_INT
        );

        $stmt->bindValue(
            ':dataReferencia',
            $dataReferenciaOriginal->format('Y-m-d')
        );

        $stmt->bindValue(
            ':horaReferencia',
            $horaReferenciaOriginal->format('H:i:s')
        );

        $stmt->execute();

        return $stmt->fetchAll();
    }
}
