<?php

declare(strict_types=1);

namespace Normalize\Log;

use RuntimeException;
use Throwable;

class LoggerTXT extends Logger
{
    public function escrever(
        string $mensagem,
        string $nivel = 'INFO',
        array $contexto = [],
        ?Throwable $excecao = null
    ): void {
        $data = date('Y-m-d H:i:s');

        $texto = sprintf(
            "[%s] [%s] %s",
            $data,
            strtoupper($nivel),
            $mensagem
        );
        
        if ($contexto !== []) {
            $contextoNormalizado = $this->normalizarContexto($contexto);

            $contextoJson = json_encode(
                $contextoNormalizado,
                JSON_UNESCAPED_UNICODE
                    | JSON_UNESCAPED_SLASHES
            );

            if ($contextoJson !== false) {
                $texto .= PHP_EOL
                    . 'Contexto: '
                    . $contextoJson;
            }
        }

        if ($excecao !== null) {
            $texto .= PHP_EOL
                . 'Exceção: '
                . $excecao->getMessage();

            $texto .= PHP_EOL
                . 'Origem: '
                . $excecao->getFile()
                . ':'
                . $excecao->getLine();

            $texto .= PHP_EOL
                . 'Stack trace:'
                . PHP_EOL
                . $excecao->getTraceAsString();
        }

        $texto .= PHP_EOL
            . str_repeat('-', 80)
            . PHP_EOL;

        $diretorio = dirname($this->caminhoArquivo);

        if (
            !is_dir($diretorio)
            && !mkdir($diretorio, 0775, true)
            && !is_dir($diretorio)
        ) {
            throw new RuntimeException(
                'Não foi possível criar o diretório de logs: '
                    . $diretorio
            );
        }

        if (!is_writable($diretorio)) {
            throw new RuntimeException(
                'O diretório de logs não possui permissão de escrita: '
                    . $diretorio
            );
        }

        $resultado = file_put_contents(
            $this->caminhoArquivo,
            $texto,
            FILE_APPEND | LOCK_EX
        );

        if ($resultado === false) {
            throw new RuntimeException(
                'Não foi possível escrever no arquivo de log: '
                    . $this->caminhoArquivo
            );
        }
    }

    private function normalizarContexto(array $contexto): array
    {
        foreach ($contexto as $chave => $valor) {
            if (is_float($valor)) {
                $contexto[$chave] = number_format($valor, 2, '.', '');
                continue;
            }

            if (is_array($valor)) {
                $contexto[$chave] = $this->normalizarContexto($valor);
            }
        }

        return $contexto;
    }
}
