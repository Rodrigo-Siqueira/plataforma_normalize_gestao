<?php
# Transforma erros em exceções
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

function criar_conexao_db() {

    try {
        # Criar conexão com banco de dados
        $conn = mysqli_connect(
            'localhost',
            'root',
            '',
            'dev_agendamento_db'
        );

        return $conn;
    } catch (mysqli_sql_exception $e) {

        return $e->getMessage();
    }
}