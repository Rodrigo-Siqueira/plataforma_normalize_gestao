<?php
require_once __DIR__ . '/../../config/config.php';
require_once 'regras_produto.php';
// Recebe os produtos do banco
$produtos = listar_produtos();

// Nome da página e inclui o cabeçalho
$currentPage = 'produtos';
include_once ROOT_PATH . '/includes/header.php';

// Variáveis recebe os caminhos dos templates
$tmp_container = TEMPLATE_PATH . 'tbl_container.html';
$tmp_tabela = TEMPLATE_PATH . 'tabela.html';
$lista_produtos = TEMPLATE_PATH . 'lista_produtos.html';

// Cria as variáveis para substituir nos templates
$container_titulo = 'Gestão de Produtos';
$btn_novo = 'Novo Produto';
$url_form = 'prod_form.php';

// Variéveis da tabela
$tbl_descricao = <<<THEAD
<td><b>AÇÕES</b></td>
<td><b>ID</b></td>
<td><b>NOME</b></td>
<td><b>TIPO</b></td>
<td><b>SESSÃO</b></td>
<td><b>PREÇO</b></td>
<td><b>VALIDADE</b></td>
<td><b>ATIVO</b></td>
THEAD;

$tbl_item = <<<TBODY
<tr>
    <td>
        <a href="prod_form.php?action=edit&id={ID}" class="w3-text-blue w3-small">
            <i class="material-symbols-outlined">edit</i>
        </a>
    </td>
    <td>{ID}</td>
    <td>{NOME}</td>
    <td>{TIPO}</td>
    <td>{NRO_SESSOES}</td>
    <td>{PRECO}</td>
    <td>{VALIDADE}</td>
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

    // Verifica se tem produtos para listar
    if ($produtos) {
        if (file_exists($tmp_tabela)) {

            $tabela_produto = file_get_contents($tmp_tabela);
            $tabela_produto = str_replace('{DESCRICAO}', $tbl_descricao, $tabela_produto);

            foreach ($produtos as $produto) {
                $item = $tbl_item;
                $item = str_replace('{ID}', htmlspecialchars($produto['id'], ENT_QUOTES, 'UTF-8'), $item);
                $item = str_replace('{NOME}', htmlspecialchars($produto['nome'], ENT_QUOTES, 'UTF-8'), $item);
                $item = str_replace('{TIPO}', htmlspecialchars($produto['tipo'], ENT_QUOTES, 'UTF-8'), $item);
                $item = str_replace('{NRO_SESSOES}', htmlspecialchars($produto['numero_sessoes'], ENT_QUOTES, 'UTF-8'), $item);
                $item = str_replace('{PRECO}', htmlspecialchars('R$ ' . number_format($produto['preco'], 2, ',', '.'), ENT_QUOTES, 'UTF-8'), $item);
                $item = str_replace('{VALIDADE}', htmlspecialchars($produto['validade_dias'], ENT_QUOTES, 'UTF-8'), $item);
                $item = str_replace('{ATIVO}', htmlspecialchars($produto['ativo'], ENT_QUOTES, 'UTF-8'), $item);

                $itens .= $item;
            }
            $tabela_produto = str_replace('{ITENS}', $itens, $tabela_produto);

            $lista = str_replace('{TABELA}', $tabela_produto, $lista);
        } else {
            print "Template não encontrado.";
        }
    } else {
        $html = '<p class="w3-center">Não existe produtos cadastrados.</p>';
        $lista = str_replace('{TABELA}', $html, $lista);
    }

    print $lista;
} else {
    print "Template não encontrado.";
}

include_once ROOT_PATH . '/includes/footer.php';