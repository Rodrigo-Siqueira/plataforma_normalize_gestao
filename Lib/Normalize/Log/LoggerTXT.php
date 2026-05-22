<?php

namespace Normalize\Log;

class LoggerTXT extends Logger
{
    public function escrever(string $mensagem): void
    {
        $data = date('Y-m-d H:i:s');
        $texto = "[$data] SQL: {$mensagem}\n";

        // Salva em um arquivo .txt fisicamente
        file_put_contents($this->caminhoArquivo, $texto, FILE_APPEND);
    }
}