<?php

declare(strict_types=1);

namespace App\Config;

use LogicException;

final class Cronometro
{
    private int $inicio;

    private function __construct()
    {
        $this->inicio = hrtime(true);
    }

    public static function iniciar(): self
    {
        return new self();
    }

    public function finalizarEmMilissegundos(): float
    {
        $fim = hrtime(true);

        if ($fim < $this->inicio) {
            throw new LogicException(
                'Não foi possível calcular o tempo da operação.'
            );
        }

        return round(
            ($fim - $this->inicio) / 1_000_000,
            2
        );
    }
}
