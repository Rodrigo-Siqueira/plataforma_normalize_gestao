<?php

declare(strict_types=1);

namespace Normalize\Log;

abstract class Logger
{
    public function __construct(
        protected string $caminhoArquivo
    ) {}

    abstract public function escrever(
        string $mensagem,
        string $nivel = 'INFO',
        array $contexto = [],
        ?\Throwable $excecao = null
    ): void;
}
