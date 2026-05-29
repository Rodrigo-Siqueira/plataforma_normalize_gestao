<?php

declare(strict_types=1);

namespace App\Repository;

use App\Model\PessoaModel;
use Exception;
use Normalize\Database\Transacao;

class PessoaRepository
{
    public function salvar(PessoaModel $pessoa): int
    {
        $pdo = Transacao::pegarConexaoAtiva();
        $sql = "INSERT INTO pessoa (nome_completo, contato) VALUES (:nome, :contato)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':nome', $pessoa->getNome());
        $stmt->bindValue(':contato', $pessoa->getContato());

        if (!$stmt->execute()) {
            throw new Exception("Falha ao inserir pessoa no banco de dados.");
        }

        return (int) $pdo->lastInsertId();
    }

    public function alterar(PessoaModel $pessoa): bool
    {
        if ($pessoa->getId() === null) {
            throw new Exception("Não é possível atualizar uma pessoa sem ID.");
        }

        $pdo = Transacao::pegarConexaoAtiva();

        $sql = "UPDATE pessoa SET nome_completo = :nome, contato = :contato WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':nome', $pessoa->getNome());
        $stmt->bindValue(':contato', $pessoa->getContato());
        $stmt->bindValue(':id', $pessoa->getId(), \PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function buscarPorId(int $id): ?PessoaModel
    {
        $pdo = Transacao::pegarConexaoAtiva();

        $sql = "SELECT id, nome_completo, contato FROM pessoa WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', $id, \PDO::PARAM_INT);
        $stmt->execute();

        $dados = $stmt->fetch();

        if (!$dados) {
            return null;
        }

        return new PessoaModel(
            id: (int) $dados->id,
            nome: $dados->nome_completo,
            contato: $dados->contato
        );
    }

    public function buscarPorNomeEContato(string $nome, string $contato): ?PessoaModel
    {
        $pdo = Transacao::pegarConexaoAtiva();

        $sql = "SELECT id, nome_completo, contato FROM pessoa WHERE nome_completo = :nome AND contato = :contato";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':nome', $nome);
        $stmt->bindValue(':contato', $contato);
        $stmt->execute();

        $dados = $stmt->fetch();

        if (!$dados) {
            return null;
        }

        return new PessoaModel(
            id: (int) $dados->id,
            nome: $dados->nome_completo,
            contato: $dados->contato
        );
    }
}