<?php
// Adicniona arquivos com as Constantes
require_once __DIR__ . '/../../config/config.php';
require_once 'regras_psicologo.php';
// Carrega os psicologos cadastrados
$psicologos = listar_psicologo();

// Nome da página e inclui o cabeçalho
$currentPage = 'psicologo';
include_once ROOT_PATH . '/includes/header.php';

// Variáveis recebe os caminhos dos templates
$tmp_container = TEMPLATE_PATH . 'tbl_container.html';
$tmp_tabela = TEMPLATE_PATH . 'tabela.html';
$lista_produtos = TEMPLATE_PATH . 'lista_psicologo.html';

// Cria as variáveis para substituir nos templates
$container_titulo = 'Gestão de Psicólogos';
$btn_novo = 'Novo Psicólogo';
$url_form = 'psicologo_form.php';

// Variéveis da tabela
$tbl_descricao = <<<THEAD
<td><b>AÇÕES</b></td>
<td><b>ID</b></td>
<td><b>NOME</b></td>
<td><b>CRP</b></td>
<td><b>CONTATO</b></td>
<td><b>ATIVO</b></td>
THEAD;

$tbl_linha = <<<TBODY
<tr>
    <td>
        <a href="psicologo_form.php?action=edit&id={ID}" class="w3-text-blue w3-small">
            <span class="material-symbols-outlined">edit</span>
        </a>
    </td>
    <td>{ID}</td>
    <td>{NOME}</td>
    <td class="crp">{CRP}</td>
    <td class="contato">{CONTATO}</td>
    <td>{ATIVO}</td>

</tr>
TBODY;
$itens = '';

if (file_exists($tmp_container)) {
    // Variável recebe o template html
    $lista = file_get_contents($tmp_container);
    $lista = str_replace('{GESTAO_DE}', htmlspecialchars($container_titulo, ENT_QUOTES, 'UTF-8'), $lista);
    $lista = str_replace('{CADASTRAR}', htmlspecialchars($url_form, ENT_QUOTES, 'UTF-8'), $lista);
    $lista = str_replace('{BOTAO_NOVO}', htmlspecialchars($btn_novo, ENT_QUOTES, 'UTF-8'), $lista);

    // Verifica se tem psicólogos para listar
    if ($psicologos) {
        if (file_exists($tmp_tabela)) {
            $tabela_psicologo = file_get_contents($tmp_tabela);
            $tabela_psicologo = str_replace('{DESCRICAO}', $tbl_descricao, $tabela_psicologo);

            // Adiciona todos os psicolos que vieram do banco
            foreach ($psicologos as $psicologo) {
                $item = $tbl_linha;
                $item = str_replace('{ID}', htmlspecialchars($psicologo['id'], ENT_QUOTES, 'UTF-8'), $item);
                $item = str_replace('{NOME}', htmlspecialchars($psicologo['nome_completo'], ENT_QUOTES, 'UTF-8'), $item);
                $item = str_replace('{CRP}', htmlspecialchars($psicologo['crp'], ENT_QUOTES, 'UTF-8'), $item);
                $item = str_replace('{CONTATO}', htmlspecialchars($psicologo['contato'], ENT_QUOTES, 'UTF-8'), $item);
                $item = str_replace('{ATIVO}', htmlspecialchars(($psicologo['ativo'] == 1) ? 'Sim' : 'Não', ENT_QUOTES, 'UTF-8'), $item);
                $itens .= $item;
            }
        } else {
            print "Template não encontrado.";
        }
        $tabela_psicologo = str_replace('{ITENS}', $itens, $tabela_psicologo);

        $lista = str_replace('{TABELA}', $tabela_psicologo, $lista);
    } else {
        $html = '<p class="w3-center">Não existe psicologos cadastrados.</p>';
        $lista = str_replace('{TABELA}', $html, $lista);
    }
    print $lista;
} else {
    print "Template não encontrado.";
}

include_once ROOT_PATH . '/includes/footer.php';