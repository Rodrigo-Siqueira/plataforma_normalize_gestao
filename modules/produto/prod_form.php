<?php
// Inclui Constantes personalisadas e regras de produtos
require_once __DIR__ . '/../../config/config.php';
require_once 'regras_produto.php';
require_once 'processa_dados.php';

// Nome da página
$currentPage = 'produtos';

/* Se o request action for falso vai para o else 
 * e instancia a variável com os valores padrão.
 * Caso contrário faz as verificações de Action
**/
if (!empty($_REQUEST['action'])) {

    if ($_REQUEST['action'] == 'save') {
        // Pega os dados de POST para validar
        $validarInput = $_POST;
        $resultValidacao = processarDados($validarInput);
        // Recebe os erros da validação
        $errosValidacao = $resultValidacao['erros'];
        // Recebe os dados tratados
        $produto = $resultValidacao['dados'];

        // verifica se tem erros
        if (empty($errosValidacao)) {
            if (empty($produto['id'])) {
                // Chamar a função para adicionar o produto
                $resultado = adicionar_produto($produto);
            } else {
                // Chamar a função para atualisar o produto
                $resultado = alterar_produto($produto);
            }

            if ($resultado['success']) {
                print "<script>
                    alert('" . addslashes($resultado['message']) . "');
                    window.location.href = 'index.php';
                </script>";
            } else {
                print "<script>
                    alert('" . addslashes($resultado['error']) . "');
                    window.location.href = 'index.php';
                </script>";
            }
        }
  
    } elseif ($_REQUEST['action'] == 'edit') {
        $id = (int) $_GET['id'];
        // Variável recebe os dados do produto que está no banco
        $resultado = buscar_produto_id($id);
        if ($resultado) {
            $produto['id'] = $resultado['id'];
            $produto['nome'] = $resultado['nome'];
            $produto['tipo'] = $resultado['tipo'];
            $produto['sessoes'] = $resultado['numero_sessoes'];
            $produto['preco'] = $resultado['preco'];
            $produto['validade'] = $resultado['validade_dias'];
            $produto['ativo'] = $resultado['ativo'];
        }
    }
} else {

    $produto = array(
        "id"       => '',
        "nome"     => '',
        "tipo"     => '',
        "sessoes"  => '',
        "preco"    => 0,
        "validade" => 30,
        "ativo"    => 1,
    );
}

include_once ROOT_PATH . '/includes/header.php';

// Definição das opções 
$opcoes_ativo = array(
    0 => 'Não',
    1 => 'Sim'
);

$opcoes_tipos = array(
    'PACOTE' => 'PACOTE',
    'AVULSO' => 'AVULSO'
);

/* Pega o template na pasta template e muda os {valores} 
 * antes de renderizar o formulário.
**/
require_once ROOT_PATH . '/modules/form_options.php';
$tmp_form = TEMPLATE_PATH . 'form_produto.html';

if (file_exists($tmp_form)) {
    $form = file_get_contents($tmp_form);

    // Troca os Marcadores de erro por $errosValidacao
    $form = str_replace('{ERRO_ID}',       htmlspecialchars($errosValidacao['id'] ?? '', ENT_QUOTES, 'UTF-8'), $form);
    $form = str_replace('{ERRO_NOME}',       htmlspecialchars($errosValidacao['nome'] ?? '', ENT_QUOTES, 'UTF-8'), $form);
    $form = str_replace('{ERRO_ATIVO}',      htmlspecialchars($errosValidacao['ativo']  ?? '', ENT_QUOTES, 'UTF-8'), $form);
    $form = str_replace('{ERRO_TIPO}',      htmlspecialchars($errosValidacao['tipo']  ?? '', ENT_QUOTES, 'UTF-8'), $form);
    $form = str_replace('{ERRO_SESSOES}',      htmlspecialchars($errosValidacao['sessoes']  ?? '', ENT_QUOTES, 'UTF-8'), $form);
    $form = str_replace('{ERRO_PRECO}',      htmlspecialchars($errosValidacao['preco']  ?? '', ENT_QUOTES, 'UTF-8'), $form);
    $form = str_replace('{ERRO_VALIDADE}',      htmlspecialchars($errosValidacao['validade']  ?? '', ENT_QUOTES, 'UTF-8'), $form);

    // Trocas os marcadores pela variável $produto
    $form = str_replace('{id}',         htmlspecialchars($produto['id'], ENT_QUOTES, 'UTF-8'), $form);
    $form = str_replace('{nome}',       htmlspecialchars($produto['nome'], ENT_QUOTES, 'UTF-8'), $form);
    $form = str_replace('{tipo}',       options_select($opcoes_tipos, $produto['tipo']), $form);
    $form = str_replace('{qtdsessoes}', htmlspecialchars($produto['sessoes'], ENT_QUOTES, 'UTF-8'), $form);
    $form = str_replace('{preco}',      htmlspecialchars($produto['preco'], ENT_QUOTES, 'UTF-8'), $form);
    $form = str_replace('{validade}',   htmlspecialchars($produto['validade'], ENT_QUOTES, 'UTF-8'), $form);
    $form = str_replace('{ativo}',     options_select($opcoes_ativo, $produto['ativo']), $form);

    print $form;
} else {
    print "Template não encontrado.";
}

include_once ROOT_PATH . '/includes/footer.php';