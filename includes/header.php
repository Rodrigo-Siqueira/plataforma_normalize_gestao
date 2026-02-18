<?php

$header_path = TEMPLATE_PATH . 'header.html';

if (file_exists($header_path)) {
    $cabecalho = file_get_contents($header_path);
    $cabecalho = str_replace('{BASE_URL}', BASE_URL, $cabecalho);
    $cabecalho = str_replace(
        '{ACTIVE_' . $currentPage . '}',
        'w3-white',
        $cabecalho
    );

    print $cabecalho;
} else {
    print 'Template header não encontrado';
}
