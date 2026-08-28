<?php

declare(strict_types=1);

namespace App\Repository;

use App\Model\PacienteModel;
use Exception;
use Normalize\Database\Conexao;
use Normalize\Database\Transacao;

class PacienteRepository
{
    public function salvar(PacienteModel $paciente): int
    {
        $pdo = Transacao::pegarConexaoAtiva();

        $sql = "INSERT INTO paciente (
            pessoa_id,
            psicologo_responsavel_id,
            ativo)
        VALUES (:pessoaId, :psicologoRespId, :ativo)";

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':pessoaId', $paciente->getPessoaId(), \PDO::PARAM_INT);
        $stmt->bindValue(':psicologoRespId', $paciente->getPsicologoResponsavelId(), \PDO::PARAM_INT);
        $stmt->bindValue(':ativo', $paciente->isAtivo());

        if (!$stmt->execute()) {
            throw new Exception("Falha ao inserir paciente no banco de dados.");
        }

        return (int) $pdo->lastInsertId();
    }

    public function alterar(PacienteModel $paciente): bool
    {
        if ($paciente->getId() === null) {
            throw new Exception("Não é possível atualizar um paciente sem ID.");
        }

        $pdo = Transacao::pegarConexaoAtiva();

        $sql = "UPDATE paciente SET 
            psicologo_responsavel_id = :psicologoRespId,
            ativo = :ativo
        WHERE id = :id";

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':psicologoRespId', $paciente->getPsicologoResponsavelId(), \PDO::PARAM_INT);
        $stmt->bindValue(':ativo', $paciente->isAtivo());
        $stmt->bindValue(':id', $paciente->getId(), \PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function buscarPorId(int $id): ?object
    {
        try {
            $sql = "SELECT
                pa.id,
                pePac.id AS pessoaPacienteId,
                psi.id AS psicologoId,
                pePsi.nome_completo AS psicologo,
                pePac.nome_completo AS pacienteNome,
                pePac.contato AS pacienteContato,
                pa.ativo
            FROM paciente AS pa
            JOIN pessoa AS pePac ON pePac.id = pa.pessoa_id
            JOIN psicologo AS psi ON psi.id = pa.psicologo_responsavel_id
            JOIN pessoa AS pePsi ON pePsi.id = psi.pessoa_id
            WHERE pa.id = :id";

            $stmt = Conexao::abrirConexao()->prepare($sql);
            $stmt->bindValue(':id', $id, \PDO::PARAM_INT);
            $stmt->execute();

            $resultado = $stmt->fetch();
            return $resultado ?: null;
        } catch (Exception $e) {
            throw new Exception('Erro ao buscar paciente por ID: ' . $e->getMessage());
        }
    }

    public function contarTotal(?int $ativo = null): int
    {
        $sql = "SELECT COUNT(*) FROM paciente";

        if (!is_null($ativo)) {
            $sql .= " WHERE ativo = :ativo";
        }

        $stmt = Conexao::abrirConexao()->prepare($sql);

        if (!is_null($ativo)) {
            $stmt->bindValue(':ativo', $ativo, \PDO::PARAM_INT);
        }

        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }

    public function listarPaginando(int $limite, int $deslocamento): array
    {
        $sql = "SELECT
            pa.id,
            pePac.id AS pessoaPacienteId,
            pePac.nome_completo AS pacienteNome,
            pePac.contato AS pacienteContato,
            pePsi.nome_completo AS psicologo,
            pa.ativo,
            psi.id AS psicologoId,
            rp.id AS responsavelId,
            peResp.id AS responsavelPessoaId,
            peResp.nome_completo AS responsavelNome,
            peResp.contato AS responsavelContato,
            rp.tipo AS vinculo
        FROM paciente AS pa
        JOIN pessoa AS pePac ON pePac.id = pa.pessoa_id
        JOIN psicologo AS psi ON psi.id = pa.psicologo_responsavel_id
        JOIN pessoa AS pePsi ON pePsi.id = psi.pessoa_id
        LEFT JOIN responsavel_paciente AS rp ON rp.paciente_id = pa.id
        LEFT JOIN pessoa AS peResp ON peResp.id = rp.responsavel_id
        ORDER BY pePac.nome_completo 
        LIMIT :limite OFFSET :deslocamento";

        $stmt = Conexao::abrirConexao()->prepare($sql);
        $stmt->bindValue(':limite', $limite, \PDO::PARAM_INT);
        $stmt->bindValue(':deslocamento', $deslocamento, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function atualizarStatus(PacienteModel $paciente): bool
    {
        $sql = "UPDATE paciente SET ativo = :ativo WHERE id = :id";
        $stmt = Conexao::abrirConexao()->prepare($sql);
        $stmt->bindValue(':ativo', $paciente->isAtivo(), \PDO::PARAM_INT);
        $stmt->bindValue(':id', $paciente->getId());

        return $stmt->execute();
    }

    public function buscarPacientePorId(int $id): ?PacienteModel
    {
        $sql = "SELECT id, pessoa_id, psicologo_responsavel_id, ativo 
        FROM paciente WHERE id = :id";

        $stmt = Conexao::abrirConexao()->prepare($sql);
        $stmt->bindValue(':id', $id, \PDO::PARAM_INT);
        $stmt->execute();

        $dados = $stmt->fetch();

        if (!$dados) {
            return null;
        }

        return new PacienteModel(
            id: $dados->id,
            pessoaId: $dados->pessoa_id,
            psicologoResponsavelId: $dados->psicologo_responsavel_id,
            ativo: $dados->ativo
        );
    }

    public function pacienteEstaAtivo(int $pacienteId): bool
    {
        $sql = "SELECT ativo FROM paciente WHERE id = :id";
        $stmt = Conexao::abrirConexao()->prepare($sql);
        $stmt->bindValue(':id', $pacienteId, \PDO::PARAM_INT);
        $stmt->execute();

        return (bool) $stmt->fetchColumn();
    }

    public function listarPacienteEresposavel(): array 
    {
        $sql = "SELECT
            pa.id,
            pePac.nome_completo AS pacienteNome,
            rp.id AS responsavelId,
            peResp.id AS responsavelPessoaId,
            peResp.nome_completo AS responsavelNome
            FROM paciente AS pa
            JOIN pessoa AS pePac
                ON pePac.id = pa.pessoa_id
            JOIN responsavel_paciente AS rp
                ON rp.paciente_id = pa.id
            JOIN pessoa AS peResp
                ON peResp.id = rp.responsavel_id
            ORDER BY pePac.nome_completo";

        $stmt = Conexao::abrirConexao()->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll();
    }
}
