<?php

declare(strict_types=1);

namespace App\Repository;

use App\Model\ResponsavelPacienteModel;
use Exception;
use Normalize\Database\Conexao;
use Normalize\Database\Transacao;

class ResponsavelPacienteRepository
{
    public function salvar(ResponsavelPacienteModel $responsavel): int
    {
        $pdo = Transacao::pegarConexaoAtiva();

        $sql = "INSERT INTO responsavel_paciente (
            responsavel_id,
            paciente_id,
            tipo)
        VALUES (:responsavelId, :pacienteId, :parentesco)";

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':responsavelId', $responsavel->getPessoaId(), \PDO::PARAM_INT);
        $stmt->bindValue(':pacienteId', $responsavel->getPacienteId(), \PDO::PARAM_INT);
        $stmt->bindValue(':parentesco', $responsavel->getParentesco());

        if (!$stmt->execute()) {
            throw new Exception("Falha ao inserir responsável do paciente no banco de dados.");
        }

        return (int) $pdo->lastInsertId();
    }

    public function alterar(ResponsavelPacienteModel $responsavel): bool
    {
        if ($responsavel->getId() === null) {
            throw new Exception("Não é possível atualizar um responsável sem ID.");
        }

        $pdo = Transacao::pegarConexaoAtiva();

        $sql = "UPDATE responsavel_paciente SET
            responsavel_id = :responsavelId,
            paciente_id = :pacienteId,
            tipo = :parentesco
        WHERE id = :id";

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':responsavelId', $responsavel->getPessoaId(), \PDO::PARAM_INT);
        $stmt->bindValue(':pacienteId', $responsavel->getPacienteId(), \PDO::PARAM_INT);
        $stmt->bindValue(':parentesco', $responsavel->getParentesco());
        $stmt->bindValue(':id', $responsavel->getId(), \PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function buscarPorPacienteId(int $pacienteId): ?object
    {
        try {
            $sql = "SELECT
            rp.id AS responsavelId,
            peResp.id AS responsavelPessoaId,
            peResp.nome_completo AS responsavelNome,
            peResp.contato AS responsavelContato,
            rp.tipo AS vinculo
        FROM responsavel_paciente AS rp
        JOIN pessoa AS peResp ON peResp.id = rp.responsavel_id
        WHERE rp.paciente_id = :pacienteId";

            $stmt = Conexao::abrirConexao()->prepare($sql);
            $stmt->bindValue(':pacienteId', $pacienteId, \PDO::PARAM_INT);
            $stmt->execute();
            
            $resultado = $stmt->fetch();

            return $resultado ?: null;
        } catch (Exception $e) {
            throw new Exception('Erro ao buscar responsável do paciente por ID: ' . $e->getMessage());
        }
    }
}
