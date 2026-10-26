<?php

declare(strict_types=1);

namespace App\Validation;

use App\Dto\Input\RepasseInputDTO;
use App\Enum\TipoStatusRepasse;

final class RepasseValidator
{
    /**
     * @return array<string, string>
     */

    public function validar(RepasseInputDTO $repasseDto): array
    {
        $erros = [];

        if ($repasseDto->psicologoId <= 0) {
            $erros['psicologoId'] = 'Selecione um psicólogo.';
        }

        if ($repasseDto->periodoInicial > $repasseDto->periodoFinal) {
            $erros['periodoFinal'] =
                'O período final não pode ser anterior ao período inicial.';
        }

        /*
         * O valor líquido não é obrigatório durante a criação,
         * pois será calculado pelo RepasseService.
         *
         * Quando informado em uma edição, precisa ser maior que zero.
         */
        if (
            $repasseDto->valorLiquido !== null
            && $repasseDto->valorLiquido <= 0
        ) {
            $erros['valorLiquido'] =
                'O valor líquido deve ser maior que zero.';
        }

        if (
            $repasseDto->status === TipoStatusRepasse::PAGO
            && $repasseDto->dataPagamento === null
        ) {
            $erros['dataPagamento'] = 'Informe a data de pagamento para um repasse pago.';
        }

        if (
            $repasseDto->status === TipoStatusRepasse::ABERTO
            && $repasseDto->dataPagamento !== null
        ) {
            $erros['dataPagamento'] = 'Um repasse aberto não pode possuir data de pagamento.';
        }

        if (
            $repasseDto->dataPagamento !== null
            && $repasseDto->dataPagamento->format('Y-m-d')
            < $repasseDto->periodoFinal->format('Y-m-d')
        ) {
            $erros['dataPagamento'] = 'A data de pagamento não pode ser anterior ao final do período.';
        }

        return $erros;
    }
}
