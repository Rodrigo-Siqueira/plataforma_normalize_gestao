<?php

declare(strict_types=1);

namespace App\Repository;

use App\Dto\Output\PsicologoOutputDTO;
use App\Model\PsicologoModel;
use Exception;
use Normalize\Database\Conexao;
use Normalize\Database\Transacao;
use Twig\Node\Expression\ConstantExpression;

class PsicologoRepository
{
    public function listar(): array
    {
        try {
            $sql = "SELECT
                psi.id,
                psi.pessoa_id AS pessoaId,
                pe.nome_completo AS nome,
                pe.contato,
                psi.crp,
                psi.percentual_comissao AS comissao,
                psi.data_inicio AS dataInicio,
                psi.ativo
                FROM psicologo AS psi
            JOIN pessoa AS pe
            ON psi.pessoa_id = pe.id";

            $stmt = Conexao::abrirConexao()->query($sql);

            $resultadosBrutos = $stmt->fetchAll();
            return PsicologoOutputDTO::criarColecao($resultadosBrutos);
        } catch (Exception $e) {
            throw new Exception('Erro ao listar psicologos: ' . $e->getMessage());
        }
    }

    public function buscarPorId(int $id): ?PsicologoOutputDTO
    {
        try {
            $sql = "SELECT
                psi.id,
                psi.pessoa_id AS pessoaId,
                pe.nome_completo AS nome,
                pe.contato,
                psi.crp,
                psi.percentual_comissao AS comissao,
                psi.data_inicio AS dataInicio,
                psi.ativo
                FROM psicologo AS psi
            JOIN pessoa AS pe
            ON psi.pessoa_id = pe.id
            WHERE psi.id = :id";

            $stmt = Conexao::abrirConexao()->prepare($sql);
            $stmt->execute([':id' => $id]);

            $resultado = $stmt->fetch();
            return $resultado ? PsicologoOutputDTO::criarDeEntidade($resultado) : null;
        } catch (Exception $e) {
            throw new Exception('Erro ao buscar psicologo: ' . $e->getMessage());
        }
    }

    public function salvar(PsicologoModel $psicologo): int
    {
        $pdo = Transacao::pegarConexaoAtiva();

        $sql = "INSERT INTO psicologo (
            pessoa_id,
            crp,
            percentual_comissao,
            data_inicio,
            ativo)
        VALUES (:pessoaId, :crp, :comissao, :dataInicio, :ativo)";

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':pessoaId', $psicologo->getPessoaId(), \PDO::PARAM_INT);
        $stmt->bindValue(':crp', $psicologo->getCRP());
        $stmt->bindValue(':comissao', $psicologo->getComissao());
        $stmt->bindValue(':dataInicio', $psicologo->getDataInicio());
        $stmt->bindValue(':ativo', $psicologo->isAtivo(), \PDO::PARAM_INT);

        if (!$stmt->execute()) {
            throw new Exception("Falha ao inserir psicólogo no banco de dados.");
        }
        return (int) $pdo->lastInsertId();
    }

    public function alterar(PsicologoModel $psicologo): bool
    {
        if ($psicologo->getId() === null) {
            throw new Exception("Não é possível atualizar um psicólogo sem ID.");
        }

        $pdo = Transacao::pegarConexaoAtiva();

        $sql = "UPDATE psicologo SET 
            crp = :crp,
            percentual_comissao = :comissao,
            data_inicio = :dataInicio,
            ativo = :ativo
        WHERE id = :id";

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':crp', $psicologo->getCRP());
        $stmt->bindValue(':comissao', $psicologo->getComissao());
        $stmt->bindValue(':dataInicio', $psicologo->getDataInicio());
        $stmt->bindValue(':ativo', $psicologo->isAtivo(), \PDO::PARAM_INT);
        $stmt->bindValue(':id', $psicologo->getId(), \PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function buscarPsicologoPorId(int $id): ?PsicologoModel
    {
        $sql = "SELECT id, pessoa_id, crp, percentual_comissao, data_inicio, ativo 
        FROM psicologo WHERE id = :id";

        $stmt = Conexao::abrirConexao()->prepare($sql);
        $stmt->bindValue(':id', $id, \PDO::PARAM_INT);
        $stmt->execute();

        $dados = $stmt->fetch();

        if (!$dados) {
            return null;
        }

        return new PsicologoModel(
            id: (int) $dados->id,
            pessoaId: (int) $dados->pessoa_id,
            crp: (string) $dados->crp,
            comissao: (float) $dados->percentual_comissao,
            dataInicio: (string) $dados->data_inicio,
            ativo: (int) $dados->ativo
        );
    }

    public function atualizarStatus(PsicologoModel $psicologo): bool
    {
        $sql = "UPDATE psicologo SET ativo = :ativo WHERE id = :id";
        $stmt = Conexao::abrirConexao()->prepare($sql);
        $stmt->bindValue(':ativo', $psicologo->isAtivo(), \PDO::PARAM_INT);
        $stmt->bindValue(':id', $psicologo->getId());

        return $stmt->execute();
    }
}