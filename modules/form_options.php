<?php

/**
 * Gera opções HTML (<option>) para um campo select de status ativo
 *
 * @param int $status Valor que deve vir selecionado (0 = Não, 1 = Sim)
 * @return string Retorna as opções HTML prontas para o select
 */

function options_select($opcoes, $selecionado = null) {

    // Variável que irá acumular o HTML final
    $html = '';

    // Percorre o array de opções
    foreach ($opcoes as $valor => $label) {

        // Verifica se o status atual é igual à chave do array
        // Se for, adiciona o atributo "selected"
        $esta_selecionado = ($valor == $selecionado) ? 'selected' : '';

        // Monta a tag <option> com value, texto e seleção
        $html .= sprintf(
            '
            <option value="%s" %s>%s</option>',
            htmlspecialchars($valor, ENT_QUOTES, 'UTF-8'),
            $esta_selecionado,
            htmlspecialchars($label, ENT_QUOTES, 'UTF-8')
        );
    }

    // Retorna todas as opções geradas
    return $html;
}

function selecionarEntidade($entidades, $selecionado = null)
{

    // Variável que irá acumular o HTML final
    $html = '';

    // Percorre o array de opções
    if ($entidades) {
        foreach ($entidades as $entidade) {
            $selected = ($entidade['id'] == $selecionado) ? 'selected' : '';
            $html .= "<option value='{$entidade['id']}' $selected>{$entidade['nome_completo']}</option>";
        }
    }

    // Retorna todas as opções geradas
    return $html;
}

