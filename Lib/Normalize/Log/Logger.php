<?php

namespace Normalize\Log;

abstract class Logger
{
    protected string $caminhoArquivo;

    public function __construct(string $caminhoArquivo)
    {
        $this->caminhoArquivo = $caminhoArquivo;
    }

    abstract public function escrever(string $mensagem): void;
}