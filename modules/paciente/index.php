<?php
require_once __DIR__ . '/../../config/config.php';
require_once 'regras_paciente.php';

$pacientes = listarPacientes();

$currentPage = 'paciente';
include_once ROOT_PATH . '/includes/header.php';

// Variáveis recebe os caminhos dos templates
$tmpContainer = TEMPLATE_PATH . 'tbl_container.html';
$tmpTabela = TEMPLATE_PATH . 'tabela.html';

// Cria as variáveis para substituir nos templates
$containerTitulo = 'Gestão de Pacientes';
$btnNovo = 'Novo Paciente';
$urlForm = 'paciente_form.php';

// Variéveis da tabela
$tblDescricao = <<<THEAD
<td><b>AÇÕES</b></td>
<td><b>ID</b></td>
<td><b>NOME</b></td>
<td><b>CONTATO</b></td>
<td><b>PSICÓLOGO</b></td>
<td><b>RESP. FIN.</b></td>
THEAD;

$tblLinha = <<<TBODY
<tr>
    <td>
        <a href="paciente_form.php?action=edit&id={ID}" class="w3-text-blue w3-small">
            <span class="material-symbols-outlined">edit</span>
        </a>
    </td>
    <td>{ID}</td>
    <td>{NOME}</td>
    <td class="contato">{CONTATO}</td>
    <td>{PSICOLOGO}</td>
    <td>{RESPFIN}</td>

</tr>
TBODY;
$itens = '';

if (file_exists($tmpContainer)) {
    $lista = file_get_contents($tmpContainer);
    $lista = str_replace('{GESTAO_DE}', htmlspecialchars($containerTitulo, ENT_QUOTES, 'UTF-8'), $lista);
    $lista = str_replace('{CADASTRAR}', htmlspecialchars($urlForm, ENT_QUOTES, 'UTF-8'), $lista);
    $lista = str_replace('{BOTAO_NOVO}', htmlspecialchars($btnNovo, ENT_QUOTES, 'UTF-8'), $lista);

    // Verifica se tem pacientes para listar
    if ($pacientes) {
        if (file_exists($tmpTabela)) {
            $tabelaPacientes = file_get_contents($tmpTabela);
            $tabelaPacientes = str_replace('{DESCRICAO}', $tblDescricao, $tabelaPacientes);

            foreach ($pacientes as $paciente) {
                $item = $tblLinha;
                $item = str_replace('{ID}', htmlspecialchars($paciente['id'], ENT_QUOTES, 'UTF-8'), $item);
                $item = str_replace('{NOME}', htmlspecialchars($paciente['pacienteNome'], ENT_QUOTES, 'UTF-8'), $item);
                $item = str_replace('{CONTATO}', htmlspecialchars($paciente['pacienteContato'], ENT_QUOTES, 'UTF-8'), $item);
                $item = str_replace('{PSICOLOGO}', htmlspecialchars($paciente['psicologo'], ENT_QUOTES, 'UTF-8'), $item);
                $item = str_replace('{RESPFIN}', htmlspecialchars($paciente['responsavelNome'], ENT_QUOTES, 'UTF-8'), $item);
                $itens .= $item;
            }

            $lista = str_replace('{ITENS}', $tabelaPacientes, $lista);
        } else {
            print "Template não encontrado.";
        }

        $tabelaPacientes = str_replace('{ITENS}', $itens, $tabelaPacientes);
        $lista = str_replace('{TABELA}', $tabelaPacientes, $lista);
    } else {
        $html = '<p class="w3-center">Não existe psicologos cadastrados.</p>';
        $lista = str_replace('{TABELA}', $html, $lista);
    }


    print $lista;
} else {
    print "Template não encontrado.";
}

include_once ROOT_PATH . '/includes/footer.php';