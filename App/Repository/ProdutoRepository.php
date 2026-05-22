<?php

declare(strict_types=1);

namespace App\Repository;

use App\Dto\Output\ProdutoOutputDTO;
use App\Enum\TipoProduto;
use App\Model\ProdutoModel;
use Exception;
use Normalize\Database\Conexao;

class ProdutoRepository
{
    public function listar(): array
    {
        try {
            $sql = "SELECT * FROM produto ORDER BY id";
            $stmt = Conexao::abrirConexao()->query($sql);

            $resultadosBrutos = $stmt->fetchAll();
            return ProdutoOutputDTO::criarColecao($resultadosBrutos);
        } catch (Exception $e) {
            throw new Exception('Erro ao listar produtos: ' . $e->getMessage());
        }
    }

    public function buscarPorId(int $id): ?ProdutoOutputDTO
    {
        try {
            $sql = "SELECT * FROM produto WHERE id = :id";
            $stmt = Conexao::abrirConexao()->prepare($sql);
            $stmt->execute([':id' => $id]);

            $resultado = $stmt->fetch();
            return $resultado ? ProdutoOutputDTO::criarDeEntidade($resultado) : null;
        } catch (Exception $e) {
            throw new Exception('Erro ao buscar o produto: ' . $e->getMessage());
        }
    }

    public function salvar(ProdutoModel $produto): bool
    {
        try {
            $sql = "INSERT INTO produto (
                nome, 
                tipo, 
                numero_sessoes, 
                preco, 
                validade_dias, 
                ativo)
            VALUES (:nome, :tipo, :numeroSessoes, :preco, :validadeDias, :ativo)";
            $stmt = Conexao::abrirConexao()->prepare($sql);

            return $stmt->execute([
                ':nome'          => $produto->getNome(),
                ':tipo'          => $produto->getTipo()->value,
                ':numeroSessoes' => $produto->getNumeroSessoes(),
                ':preco'         => $produto->getPreco(),
                ':validadeDias'  => $produto->getValidadeDias(),
                ':ativo'         => $produto->isAtivo()
            ]);
        } catch (Exception $e) {
            throw new Exception('Erro ao salvar o produto: ' . $e->getMessage());
        }
    }

    public function alterar(ProdutoModel $produto): bool
    {
        try {
            $sql = "UPDATE produto SET 
                nome = :nome, 
                tipo = :tipo, 
                numero_sessoes = :numeroSessoes,
                preco = :preco,
                validade_dias = :validadeDias,
                ativo = :ativo 
            WHERE id = :id";
            $stmt = Conexao::abrirConexao()->prepare($sql);

            return $stmt->execute([
                ':id'            => $produto->getId(),
                ':nome'          => $produto->getNome(),
                ':tipo'          => $produto->getTipo()->value,
                ':numeroSessoes' => $produto->getNumeroSessoes(),
                ':preco'         => $produto->getPreco(),
                ':validadeDias'  => $produto->getValidadeDias(),
                ':ativo'         => $produto->isAtivo()
            ]);
        } catch (Exception $e) {
            throw new Exception('Erro ao alterar o produto: ' . $e->getMessage());
        }
    }

    public function buscarProdutoPorId(int $id): ?ProdutoModel
    {
        $sql = "SELECT * FROM produto WHERE id = :id";
        $stmt = Conexao::abrirConexao()->prepare($sql);
        $stmt->execute([':id' => $id]);
        $dados = $stmt->fetch();

        if (!$dados) {
            return null;
        }

        // Devolve a entidade real com as regras de negócio!
        return new ProdutoModel(
            id: (int) $dados->id,
            nome: $dados->nome,
            tipo: TipoProduto::from($dados->tipo),
            numeroSessoes: (int) $dados->numero_sessoes,
            preco: (float) $dados->preco,
            validadeDias: (int) $dados->validade_dias,
            ativo: (int) $dados->ativo
        );
    }

    public function atualizarStatus(ProdutoModel $produto): bool
    {
        $sql = "UPDATE produto SET ativo = :ativo WHERE id = :id";
        $stmt = Conexao::abrirConexao()->prepare($sql);
        return $stmt->execute([
            ':ativo' => $produto->isAtivo(),
            ':id' => $produto->getId()
        ]);
    }
}