<?php

// Define o fuso horário da Aplicação
date_default_timezone_set('America/Sao_Paulo');

// URLs da Aplicação
define('CAMINHO_BASE', dirname(__DIR__));
define('CAMINHO_ARQUIVO_INI',  dirname(__DIR__). '/Config/');
define('CAMINHO_TEMPLATE', dirname(__DIR__, 2) . '/Templates');
define('URL_PRODUCAO', 'http://app.agendamento');
define('URL_DESENVOLVIMENTO', 'http://dev.agendamento');
