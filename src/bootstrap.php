<?php

declare(strict_types=1);

use Normalize\Helper\Helper;
use Pecee\SimpleRouter\Exceptions\NotFoundHttpException;
use Pecee\SimpleRouter\SimpleRouter as Router;

ini_set('display_errors', 1);
error_reporting(E_ALL);

require dirname(__DIR__) . '/vendor/autoload.php';
require dirname(__DIR__) . '/Route/app.php';

try {
    Router::start();
} catch (NotFoundHttpException $e) {
    if (Helper::identifarAmbiente()) {
        print "<h1>Erro 404 - Rota não encontrada</h1>";
        print "<p>" . $e->getMessage() . "</p>";
    } else {
        Helper::redirecionar('404');
    }
    
} catch (\Throwable $e) {
    // Captura erros gerais, incluindo \Twig\Error\SyntaxError, \Twig\Error\RuntimeError, etc.
    if (Helper::identifarAmbiente()) {
        // Formatação básica e amigável para ambiente Dev
        echo "<div style='font-family: sans-serif; padding: 10px; background: #fff; color: #000000; border-left: 5px solid #000000;'>";
        print "<h2>Erro na Aplicação!</h2>";
        print "<strong>Mensagem:</strong> " . $e->getMessage() . "<br><br>";
        print "<strong>Arquivo:</strong> " . $e->getFile() . " (Linha: " . $e->getLine() . ")<br><br>";
        print "<strong>Stack Trace:</strong><pre style='background: #fff; padding: 10px; overflow-x: auto;'>" . $e->getTraceAsString() . "</pre>";
        print "</div>";
    } else {
        // error_log($e->getMessage()); 
        Helper::redirecionar('500'); // Não implementado
    }
}