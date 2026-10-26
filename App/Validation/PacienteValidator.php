<?php

declare(strict_types=1);

namespace App\Validation;

use App\Dto\Input\PacienteInputDTO;
use App\Dto\Input\PessoaInputDTO;
use App\Enum\TipoParentesco;

class PacienteValidator
{
    private array $erros = [];

    public function validar(PacienteInputDTO $dto): array
    {
        $this->validarNome('paciente', $dto->paciente);
        $this->validarContato('paciente', $dto->paciente) ;

        // Validação de PSICOLOGO RESPOSAVEL
        if ($dto->psicologoResponsavelId <= 0) {
            $this->erros['psicologoId'] = "A seleção de um psicólogo responsável é obrigatória.";
        }
        
        if ($dto->responsavel && $dto->responsavel->parentesco !== TipoParentesco::PROPRIO) {
            if ($dto->responsavel->pessoaResponsavel !== null) {
                $this->validarNome('responsavel', $dto->responsavel->pessoaResponsavel);
                $this->validarContato('responsavel', $dto->responsavel->pessoaResponsavel);
            } else {
                $this->erros['responsavelNome'] = "Os dados do responsável são obrigatórios.";
            }
        }

        return $this->erros;
    }

    private function validarNome(string $entidade, PessoaInputDTO $pessoa): void
    {
        if ($pessoa->nome === '') {
            $this->erros[$entidade . 'Nome'] = "O nome do {$entidade} é obrigatório.";
        }
    }

    private function validarContato(string $entidade, PessoaInputDTO $pessoa): void
    {
        if ($pessoa->contato === '') {
            $this->erros[$entidade . 'Contato'] = "O contato do {$entidade} é obrigatório.";
        }
    }
}
