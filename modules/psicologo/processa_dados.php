<?php
function validarPsicologo($dadosAvalidar) {
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

    // Valida ID (somente se vier preenchido)
    $pessoa_id = trim($dadosAvalidar['pessoa_id'] ?? '');

    if ($pessoa_id !== '') {
        if (!ctype_digit($pessoa_id)) {
            $erros['pessoa_id'] = 'ID pessoa inválido.';
        } elseif ((int)$pessoa_id <= 0) {
            $erros['pessoa_id'] = 'ID pessoa inválido.';
        } else {
            $dadosValidados['pessoa_id'] = (int)$pessoa_id;
        }
    }

    // Valida Ativo
    $ativo = trim($dadosAvalidar['ativo'] ?? '');
    $ativo_validos = ['0', '1'];
    if ($ativo === '') {
        $erros['ativo'] = 'O status é obrigatório.';
    } elseif (!in_array($ativo, $ativo_validos)) {
        $erros['ativo'] = 'Status inválido.';
    } else {
        $dadosValidados['ativo'] = (int) $ativo;
    }

    // valida nome
    $nome = trim($dadosAvalidar['nome'] ?? '');
    if ($nome === '') {
        $erros['nome'] = 'O nome do produto é obrigatório.';
    } elseif (strlen($nome) < 3) {
        $erros['nome'] = 'O nome deve ser maior que 3 caracteres.';
    } elseif (strlen($nome) > 100) {
        $erros['nome'] = 'O nome deve ser menor que 100 caracteres.';
    } elseif (!preg_match('/^[\p{L}\s]+$/u', $nome)) {
        $erros['nome'] = 'O nome deve conter apenas letras e espaços.';
    } else {
        $dadosValidados['nome'] = $nome;
    }

    // CRP
    $crp = trim($dadosAvalidar['crp'] ?? '');
    if ($crp === '') {
        $erros['crp'] = 'O CRP é obrigatório.';
    } elseif (strlen($crp) < 5) {
        $erros['crp'] = 'O CRP deve ser maior que 5 caracteres.';
    } elseif (!is_numeric($crp)) {
        $erros['crp'] = 'O CRP deve apenas números válido.';
    } elseif (!preg_match('/^\d{1,15}$/', $crp)) {
        $erros['crp'] = 'O CRP deve ter o máximo de 15 digitos.';
    } else {
        $dadosValidados['crp'] = $crp;
    }

    // Contato
    $contato = trim($dadosAvalidar['contato'] ?? '');
    if ($contato === '') {
        $erros['contato'] = 'O contato é obrigatório.';
    } elseif (strlen($contato) < 10) {
        $erros['contato'] = 'O contato deve ser maior que 10 caracteres.';
    } elseif (!is_numeric($contato)) {
        $erros['contato'] = 'O contato deve apenas números válido.';
    } elseif (!preg_match('/^\d{1,11}$/', $contato)) {
        $erros['contato'] = 'O contato deve ter o máximo de 11 digitos.';
    } else {
        $dadosValidados['contato'] = $contato;
    }

    // Comissão
    $comissao = trim($dadosAvalidar['comissao'] ?? '');
    if ($comissao === '') {
        $erros['comissao'] = 'A comissão é obrigatória.';
    } elseif (!is_numeric($comissao)) {
        $erros['comissao'] = 'A comissão deve ser um número válido.';
    } elseif (!preg_match('/^\d+([.,]\d{1,2})?$/', $comissao)) {
        $erros['comissao'] = 'A comissão deve ser um número positivo.';
    } else {
        // Troca vírgula por ponto
        $comissao = str_replace(',', '.', $comissao);
        $comissao = round((float)$comissao, 2);

        if ($comissao > 1.00) {
            $erros['comissao'] = 'A comissão não pode ser maior que 1.00.';
        } else {
            $dadosValidados['comissao'] = $comissao;
        }
    }

    // Data início
    $dataInicio = trim($dadosAvalidar['data_inicio'] ?? '');
    if ($dataInicio === '') {
        $erros['data_inicio'] = 'A data de início é obrigatória.';
    } else {
        $data = DateTime::createFromFormat('Y-m-d', $dataInicio);
        if (!$data || $data->format('Y-m-d') !== $dataInicio) {
            $erros['data_inicio'] = 'Data inválida.';
        } else {
            $dadosValidados['data_inicio'] = $dataInicio;
        }
    }


    return [
        "erros" => $erros,
        "dados" => $dadosValidados
    ];
}