<?php

declare(strict_types=1);

use App\Config\Caminhos;
use App\Config\MonitorRequisicao;
use Normalize\Helper\Helper;
use Normalize\Log\LoggerTXT;
use Pecee\SimpleRouter\Exceptions\NotFoundHttpException;
use Pecee\SimpleRouter\SimpleRouter as Router;

ini_set('display_errors', 1);
error_reporting(E_ALL);

require dirname(__DIR__) . '/vendor/autoload.php';

$inicioRequisicao = defined('INICIO_REQUISICAO') ? INICIO_REQUISICAO : hrtime(true);
$monitorRequisicao = new MonitorRequisicao($inicioRequisicao);

$monitorRequisicao->registrarAoFinal();
require dirname(__DIR__) . '/Router/app.php';

try {
    Router::start();
} catch (NotFoundHttpException $e) {
    if (Helper::identificarAmbiente()) {
        print "<h1>Erro 404 - Rota não encontrada</h1>";
        print "<p>" . $e->getMessage() . "</p>";
    } else {
        Helper::redirecionar('404');
    }
    
} catch (\Throwable $e) {
    // Captura erros gerais, incluindo \Twig\Error\SyntaxError, \Twig\Error\RuntimeError, etc.
    if (Helper::identificarAmbiente()) {
        // Formatação básica e amigável para ambiente Dev
        echo "<div style='font-family: sans-serif; padding: 10px; background: #fff; color: #000000; border-left: 5px solid #000000;'>";
        print "<h2>Erro na Aplicação!</h2>";
        print "<strong>Mensagem:</strong> " . $e->getMessage() . "<br><br>";
        print "<strong>Arquivo:</strong> " . $e->getFile() . " (Linha: " . $e->getLine() . ")<br><br>";
        print "<strong>Stack Trace:</strong><pre style='background: #fff; padding: 10px; overflow-x: auto;'>" . $e->getTraceAsString() . "</pre>";
        print "</div>";
    } else {
        try {
            $loggerErros = new LoggerTXT(
                Caminhos::logErros()
            );

            $loggerErros->escrever(
                mensagem: 'Erro não tratado na aplicação.',
                nivel: 'ERRO',
                contexto: [
                    'processo' => 'requisicao_http',
                    'metodo' =>
                    $_SERVER['REQUEST_METHOD'] ?? 'CLI',
                    'rota' =>
                    $_SERVER['REQUEST_URI'] ?? 'CLI',
                ],
                excecao: $e
            );
        } catch (\Throwable $erroLog) {
            error_log(
                'Falha ao registrar erro da aplicação: '
                    . $erroLog->getMessage()
            );
        }

        Helper::redirecionar('500');
    }
}