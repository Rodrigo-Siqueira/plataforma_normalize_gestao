<?php

declare(strict_types=1);

namespace App\Service;

use App\Dto\Input\PessoaInputDTO;
use App\Model\PessoaModel;
use App\Repository\PessoaRepository;

class PessoaService
{
    private PessoaRepository $repositorio;

    public function __construct()
    {
        $this->repositorio = new PessoaRepository();
    }

    public function buscarOuCriar(PessoaInputDTO $dto): int
    {
        $pessoaExistente = $this->repositorio->buscarPorNomeEContato($dto->nome, $dto->contato);

        if ($pessoaExistente !== null) {
            return $pessoaExistente->getId();
        }

        return $this->salvar($dto);
    }
    
    public function salvar(PessoaInputDTO $dto): int
    {
        $pessoa  = new PessoaModel(
            id: $dto->id,
            nome: $dto->nome,
            contato: $dto->contato
        );

        if ($pessoa->getId() === null){
            return $this->repositorio->salvar($pessoa);
        }

        $this->repositorio->alterar($pessoa);
        return $pessoa->getId();
    }
}