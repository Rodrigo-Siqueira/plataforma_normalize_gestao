<?php

function processarDados($dadosAvalidar) {
    $erros = array();
    $dadosValidados = array();

    // Criar uma cópia de de $dadosAvalidar
    // em $dadosValidados
    foreach ($dadosAvalidar as $chave => $valor) {
        $dadosValidados[$chave] = $valor;
    }

    // Valida ID (somente se vier preenchido)
    $id = trim($dadosAvalidar['id'] ?? '');

    if ($id !== '') {
        if (!ctype_digit($id)) {
            $erros['id'] = 'ID inválido.';
        } elseif ((int)$id <= 0) {
            $erros['id'] = 'ID inválido.';
        } else {
            $dadosValidados['id'] = (int)$id;
        }
    }

    // valida nome
    $nome = trim($dadosAvalidar['nome'] ?? '');
    if (empty($nome)) {
        $erros['nome'] = 'O nome do produto é obrigatório';
    } elseif (strlen($nome) < 3) {
        $erros['nome'] = 'O nome deve ser maior que 3 caracteres';
    } elseif (strlen($nome) > 50) {
        $erros['nome'] = 'O nome deve ser menor que 50 caracteres';
    } elseif (!preg_match('/^[\p{L}\s]+$/u', $nome)) {
        $erros['nome'] = 'O nome deve conter apenas letras e espaços.';
    } else {
        $dadosValidados['nome'] = $nome;
    }

    // Valida Ativo
    $ativo = trim($dadosAvalidar['ativo'] ?? '');
    $ativo_validos = ['0', '1'];
    if ($ativo === '') {
        $erros['ativo'] = 'O status de ativo é obrigatório.';
    } elseif (!in_array($ativo, $ativo_validos)) {
        $erros['ativo'] = 'Status inválido.';
    } else {
        $dadosValidados['ativo'] = (int) $ativo;
    }

    // Valida Tipo
    $tipo = trim($dadosAvalidar['tipo'] ?? '');
    $tipos_validos = ['PACOTE', 'AVULSO'];
    if (empty($tipo)) {
        $erros['tipo'] = 'O tipo é obrigatório.';
    } elseif (!in_array($tipo, $tipos_validos)) {
        $erros['tipo'] = 'Tipo de produto inválido.';
    } else {
        $dadosValidados['tipo'] = $tipo;
    }

    // Valida Qtde sessão
    $sessoes = trim($dadosAvalidar['sessoes'] ?? '');
    if ($sessoes === '') {
        $erros['sessoes'] = 'Quantidade de sessões é obrigatória.';
    } elseif (!is_numeric($sessoes)) {
        $erros['sessoes'] = 'Quantidade de sessões deve ser um número válido.';
    } elseif (!preg_match('/^\d{1,2}$/', $sessoes)) {
        $erros['sessoes'] = 'A quantidade de sessões máxima 2 digitos.';
    } else {
        $dadosValidados['sessoes'] = (int) $sessoes;

        if ($dadosValidados['sessoes'] < 1) {
            $erros['sessoes'] = 'Minino de sessões deve ser 1 dia.';
        } elseif ($dadosValidados['sessoes'] > 52) {
            $erros['sessoes'] = 'Máximo de sessões deve ser até 52 dia.';
        }
    }

    // VAlida Preço
    $preco = trim($dadosAvalidar['preco'] ?? '');
    if ($preco === '') {
        $erros['preco'] = 'Preço é obrigatório,';
    } elseif (!is_numeric($preco)) {
        $erros['preco'] = 'O preço deve ser um número válido.';
    } elseif (!preg_match('/^\d+([.,]\d{1,2})?$/', $preco)) {
        $erros['preco'] = 'O preço deve ser um número positivo.';
    } else {
        $dadosValidados['preco'] = round((float)$preco, 2);

        if ($dadosValidados['preco'] > 5000.00) {
            $erros['preco'] = 'Preço máximo permitido é R$ 5.000,00.';
        }
    }

    // Valida Validade
    $validade = trim($dadosAvalidar['validade'] ?? '');
    if ($validade === '') {
        $erros['validade'] = 'A validade é obrigatória.';
    } elseif (!is_numeric($validade)) {
        $erros['validade'] = 'A validade deve ser um número inteiro.';
    } elseif (!preg_match('/^\d{1,2}$/', $validade)) {
        $erros['validade'] = 'O máxima é 2 digitos.';
    } else {
        $dadosValidados['validade'] = (int)$validade;

        if ($dadosValidados['validade'] < 30) {
            $erros['validade'] = 'Validade não pode ser menor que 30 dias.';
        } elseif ($dadosValidados['validade'] > 90) {
            $erros['validade'] = 'Validade não pode ser menor que 30 dias.';
        }
    }

    return [
        "erros" => $erros,
        "dados" => $dadosValidados
    ];
}