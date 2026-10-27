<?php

declare(strict_types=1);

namespace App\Config;

use Normalize\Log\Logger;
use Normalize\Log\LoggerTXT;

final class MonitorRequisicao
{
    private int $inicioExecucao;

    private Logger $loggerDesempenho;

    private bool $finalizado = false;

    public function __construct(
        ?int $inicioExecucao = null
    ) {
        $this->inicioExecucao = $inicioExecucao ?? hrtime(true);
        $this->loggerDesempenho = new LoggerTXT(Caminhos::logDesempenho());
    }

    public function registrarAoFinal(): void
    {
        register_shutdown_function(
            [$this, 'finalizar']
        );
    }

    public function finalizar(): void
    {
        if ($this->finalizado) {
            return;
        }

        $this->finalizado = true;

        $duracaoMs = round(
            (
                hrtime(true)
                - $this->inicioExecucao
            ) / 1_000_000,
            2
        );

        $uri = (string) (
            $_SERVER['REQUEST_URI'] ?? 'CLI'
        );

        $rota = parse_url(
            $uri,
            PHP_URL_PATH
        );

        if ($rota === '/favicon.ico') {
            return;
        }

        $codigoHttp = http_response_code();

        if ($codigoHttp === false) {
            $codigoHttp = 200;
        }

        try {
            $this->loggerDesempenho->escrever(
                mensagem: 'Tempo total da requisição.',
                nivel: 'DESEMPENHO',
                contexto: [
                    'processo' => 'requisicao_http',
                    'metodo' =>
                    $_SERVER['REQUEST_METHOD'] ?? 'CLI',
                    'rota' => $rota,
                    'codigoHttp' => $codigoHttp,
                    'duracaoMs' => $duracaoMs,
                    'memoriaPicoMb' => round(
                        memory_get_peak_usage(true)
                            / 1024
                            / 1024,
                        2
                    ),
                ]
            );
        } catch (\Throwable $erro) {
            error_log(
                'Falha ao registrar desempenho: '
                    . $erro->getMessage()
            );
        }
    }
}
