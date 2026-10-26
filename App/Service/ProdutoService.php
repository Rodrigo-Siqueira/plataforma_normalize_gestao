<?php

declare(strict_types=1);

namespace App\Service;

use App\Dto\Input\ProdutoInputDTO;
use App\Enum\TipoProduto;
use App\Model\ProdutoModel;
use App\Repository\ProdutoRepository;
use Exception;

class ProdutoService
{
    private ProdutoRepository $repositorio;

    public function __construct()
    {
        $this->repositorio = new ProdutoRepository();
    }
    public function salvar(ProdutoInputDTO $dto)
    {
        $tipoEnum = TipoProduto::from($dto->tipo);

        $produto = new ProdutoModel(
            id: $dto->id,
            nome: $dto->nome,
            tipo: $tipoEnum,
            numeroSessoes: $dto->numeroSessoes,
            preco: $dto->preco,
            validadeDias: $dto->validadeDias,
            ativo: $dto->ativo
        );

        if ($produto->getId() === null) {
            $this->repositorio->salvar($produto);
        } else {
            $this->repositorio->alterar($produto);
        }
        
    }

    public function excluir(int $id): void
    {
        $produto = $this->repositorio->buscarProdutoPorId($id);
        if (!$produto) {
            throw new Exception("Produto não encontrado.");
        }

        $produto->desativar();
        $this->repositorio->atualizarStatus($produto);
    }
}
