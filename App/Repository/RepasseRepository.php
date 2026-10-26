<?php

declare(strict_types=1);

namespace App\Repository;

use App\Enum\TipoStatusRepasse;
use App\Model\RepasseModel;
use DateTimeImmutable;
use Exception;
use Normalize\Database\Conexao;
use Normalize\Database\Transacao;

class RepasseRepository
{
    public function salvar(RepasseModel $repasse): int
    {
        $pdo = Transacao::pegarConexaoAtiva();

        $sql = "INSERT INTO lote_repasse (
            psicologo_id,
            periodo_inicio,
            periodo_fim,
            valor_liquido_pago,
            status,
            data_pagamento
        ) VALUES (
            :psicologoId,
            :periodoInicio,
            :periodoFim,
            :valorLiquidoPago,
            :status,
            :dataPagamento
        )";
        $dataPagamento = $repasse->getDataPagamento();
        $stmt = $pdo->prepare($sql);

        $stmt->bindValue(':psicologoId', $repasse->getPsicologoId(), \PDO::PARAM_INT);
        $stmt->bindValue(':periodoInicio', $repasse->getPeriodoInicial()->format('Y-m-d'));
        $stmt->bindValue(':periodoFim', $repasse->getPeriodoFinal()->format('Y-m-d'));
        $stmt->bindValue(':valorLiquidoPago', $repasse->getValorLiquido());
        $stmt->bindValue(':status', $repasse->getStatus()->value);
        $stmt->bindValue(
            ':dataPagamento',
            $dataPagamento?->format('Y-m-d H:i:s'),
            $dataPagamento === null ? \PDO::PARAM_NULL : \PDO::PARAM_STR
        );

        if (!$stmt->execute()) {
            throw new Exception("Falha ao inserir o repasse no banco de dados.");
        }

        return (int) $pdo->lastInsertId();
    }

    public function atualizarValorLiquido(RepasseModel $repasse): bool
    {
        if ($repasse->getId() === null) {
            throw new Exception(
                'O código do repasse é obrigatório para alteração.'
            );
        }

        $sql = "UPDATE lote_repasse SET
            valor_liquido_pago = :valorLiquidoPago
        WHERE id = :id";

        $stmt = Conexao::abrirConexao()->prepare($sql);
        $stmt->bindValue(':id', $repasse->getId(), \PDO::PARAM_INT);
        $stmt->bindValue(':valorLiquidoPago', $repasse->getValorLiquido());

        return $stmt->execute();
    }


    public function buscarRepassePorId(int $id): ?object
    {
        try {
            $sql = "SELECT
                l.id,
                l.psicologo_id,
                pessoa.nome_completo AS psicologo_nome,
                l.periodo_inicio,
                l.periodo_fim,
                l.valor_liquido_pago,
                l.status,
                l.data_pagamento,
                l.data_geracao
            FROM lote_repasse AS l
            INNER JOIN psicologo AS psicologo
                ON psicologo.id = l.psicologo_id
            INNER JOIN pessoa AS pessoa
                ON pessoa.id = psicologo.pessoa_id
            WHERE l.id = :id";
            
            $stmt = Conexao::abrirConexao()->prepare($sql);
            $stmt->bindValue(':id', $id, \PDO::PARAM_INT);
            $stmt->execute();

            $resultado = $stmt->fetch();

            return $resultado ?: null;
        } catch (Exception $e) {
            throw new Exception('Houve um erro ao buscar repasse ' . $e->getMessage());
        }
    }

    public function buscarSessoesDoPsicologoParaGerarRepasse(
        int $psicologoId,
        DateTimeImmutable $periodoInicial,
        DateTimeImmutable $periodoFinal
    ): array {
        $sql = "SELECT
            s.id AS sessao_id,
            s.psicologo_id,
            s.data_agendada,
            v.id AS venda_id,
            v.valor_cobrado,
            prod.numero_sessoes,
            psi.percentual_comissao
        FROM sessao AS s
        INNER JOIN venda AS v
            ON v.id = s.venda_origem_id
        INNER JOIN produto AS prod
            ON prod.id = v.produto_id
        INNER JOIN psicologo AS psi
            ON psi.id = s.psicologo_id
        WHERE s.psicologo_id = :psicologoId
          AND s.status = 'REALIZADA'
          AND s.lote_repasse_id IS NULL
          AND s.data_agendada
              BETWEEN :periodoInicial AND :periodoFinal
        ORDER BY
            s.data_agendada ASC,
            s.hora_agendada ASC,
            s.id ASC";
        $stmt = Conexao::abrirConexao()->prepare($sql);

        $stmt->bindValue(':psicologoId', $psicologoId, \PDO::PARAM_INT);
        $stmt->bindValue(':periodoInicial', $periodoInicial->format('Y-m-d'));
        $stmt->bindValue(':periodoFinal', $periodoFinal->format('Y-m-d'));
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function listarSessoesDisponiveisParaRepasse(): array
    {
        $sql = "SELECT
            s.id AS sessao_id,
            s.paciente_id,
            s.psicologo_id,
            s.venda_origem_id,
            paciente_pessoa.nome_completo AS paciente_nome,
            psicologo_pessoa.nome_completo AS psicologo_nome,
            s.data_agendada,
            s.hora_agendada,
            s.status
        FROM sessao AS s
        INNER JOIN paciente AS paciente
            ON paciente.id = s.paciente_id
        INNER JOIN pessoa AS paciente_pessoa
            ON paciente_pessoa.id = paciente.pessoa_id
        INNER JOIN psicologo AS psicologo
            ON psicologo.id = s.psicologo_id
        INNER JOIN pessoa AS psicologo_pessoa
            ON psicologo_pessoa.id = psicologo.pessoa_id
        WHERE s.status = 'REALIZADA'
          AND s.lote_repasse_id IS NULL
        ORDER BY
            s.data_agendada ASC,
            s.hora_agendada ASC,
            s.id ASC";
        $stmt = Conexao::abrirConexao()->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function listarSessoesDoLote(int $loteRepasseId): array
    {
        $sql = "SELECT
            s.id AS sessao_id,
            s.lote_repasse_id,
            s.paciente_id,
            s.psicologo_id,
            paciente_pessoa.nome_completo AS paciente_nome,
            psicologo_pessoa.nome_completo AS psicologo_nome,
            s.data_agendada,
            s.hora_agendada,
            s.status,
            v.valor_cobrado,
            prod.numero_sessoes,
            psi.percentual_comissao,
            ROUND(
                (v.valor_cobrado / prod.numero_sessoes)
                * psi.percentual_comissao,
                2
            ) AS valor_repasse
        FROM sessao AS s
        INNER JOIN paciente AS paciente
            ON paciente.id = s.paciente_id
        INNER JOIN pessoa AS paciente_pessoa
            ON paciente_pessoa.id = paciente.pessoa_id
        INNER JOIN psicologo AS psi
            ON psi.id = s.psicologo_id
        INNER JOIN pessoa AS psicologo_pessoa
            ON psicologo_pessoa.id = psi.pessoa_id
        INNER JOIN venda AS v
            ON v.id = s.venda_origem_id
        INNER JOIN produto AS prod
            ON prod.id = v.produto_id
        WHERE s.lote_repasse_id = :loteRepasseId
        ORDER BY
            s.data_agendada ASC,
            s.hora_agendada ASC,
            s.id ASC";
        $stmt = Conexao::abrirConexao()->prepare($sql);

        $stmt->bindValue(':loteRepasseId', $loteRepasseId, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function listarPaginado(int $limite, int $deslocamento): array
    {
        try {
            $sql = "SELECT
                l.id,
                l.psicologo_id,
                pe.nome_completo AS psicologo_nome,
                l.periodo_inicio,
                l.periodo_fim,
                l.valor_liquido_pago,
                l.status,
                l.data_pagamento,
                l.data_geracao
            FROM lote_repasse AS l
            INNER JOIN psicologo AS psi
                ON psi.id = l.psicologo_id
            INNER JOIN pessoa AS pe
                ON pe.id = psi.pessoa_id
            ORDER BY
                l.data_geracao DESC,
                l.id DESC
            LIMIT :limite OFFSET :deslocamento";

            $stmt = Conexao::abrirConexao()->prepare($sql);
            $stmt->bindValue(':limite', $limite, \PDO::PARAM_INT);
            $stmt->bindValue(':deslocamento', $deslocamento, \PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll();
        } catch (Exception $e) {
            throw new Exception('Houve um erro ao listar os repasses.' . $e->getMessage());
        }
    }

    public function contarTotal(?TipoStatusRepasse $status = null): int
    {
        $sql = "SELECT COUNT(*) FROM lote_repasse";
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

    public function vincularSessoesAoLote(int $loteRepasseId, array $sessoesIds): void
    {
        if ($sessoesIds === []) {
            throw new Exception('Nenhuma sessão foi informada para o repasse.');
        }

        $pdo = Transacao::pegarConexaoAtiva();

        $sql = "UPDATE sessao SET 
            lote_repasse_id = :loteRepasseId
        WHERE id = :sessaoId
        AND status = 'REALIZADA'
        AND lote_repasse_id IS NULL";

        $stmt = $pdo->prepare($sql);

        foreach ($sessoesIds as $sessaoId) {
            $stmt->bindValue(':loteRepasseId', $loteRepasseId, \PDO::PARAM_INT);
            $stmt->bindValue(':sessaoId', (int) $sessaoId, \PDO::PARAM_INT);

            $stmt->execute();

            if ($stmt->rowCount() !== 1) {
                throw new Exception(
                    "Não foi possível vincular a sessão {$sessaoId} ao lote de repasse."
                );
            }
        }
    }

    public function atualizarStatus(RepasseModel $repasse): bool
    {
        if ($repasse->getId() === null) {
            throw new Exception(
                'O código do repasse é obrigatório para atualizar o status.'
            );
        }

        $sql = "UPDATE lote_repasse SET
            status = :status,
            data_pagamento = :dataPagamento
        WHERE id = :id";

        $stmt = Conexao::abrirConexao()->prepare($sql);
        $stmt->bindValue(':id', $repasse->getId(), \PDO::PARAM_INT);
        $stmt->bindValue(':status', $repasse->getStatus()->value);

        $dataPagamento = $repasse->getDataPagamento();

        $stmt->bindValue(
            ':dataPagamento',
            $dataPagamento?->format('Y-m-d H:i:s'),
            $dataPagamento === null ? \PDO::PARAM_NULL : \PDO::PARAM_STR
        );

        return $stmt->execute();
    }
}
