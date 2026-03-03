<?php
// Inclui Constantes personalisadas e regras de produtos
require_once __DIR__ . '/../../config/config.php';
require_once 'regras_psicologo.php';
require_once 'processa_dados.php';

// Nome da página
$currentPage = 'psicologo';
/* Se o request action for falso vai para o else 
 * e instancia a variável com os valores padrão.
 * Caso contrário faz as verificações de Action
**/
if (!empty($_REQUEST['action'])) {

    if ($_REQUEST['action'] == 'save') {
        // Pega os dados de POST para validar
        $validarInput = $_POST;
        $resultValidacao = validarPsicologo($validarInput);
        // Recebe os erros da validação
        $errosValidacao = $resultValidacao['erros'];
        // Recebe os dados tratados
        $psicologo = $resultValidacao['dados'];

        // Verifica se tem erros
        if (empty($errosValidacao)) {
            if (empty($psicologo['id'])) {
                // Chamar a função para adicionar o psicologo
                $resultado = adicionar_psicologo($psicologo);
            } else {
                // Chamar a função para editar o psicologo
                $resultado = alterar_psicologo($psicologo);
            }

            if ($resultado['sucesso']) {
                print "<script>
                    alert('" . addslashes($resultado['mensagem']) . "');
                    window.location.href = 'index.php';
                </script>";
            } else {
                print "<script>
                    alert('" . addslashes($resultado['erro']) . "');
                    window.location.href = 'index.php';
                </script>";
            }
        }

    } elseif ($_REQUEST['action'] == 'edit') {
        $id = (int) $_GET['id'];
        // Variável recebe os dados do produto que está no banco
        $resultado = buscar_psicologo_id($id);
        if ($resultado) {
            $psicologo['id'] = $resultado['id'];
            $psicologo['pessoa_id'] = $resultado['pessoa_id'];
            $psicologo['nome'] = $resultado['nome_completo'];
            $psicologo['crp'] = $resultado['crp'];
            $psicologo['contato'] = $resultado['contato'];
            $psicologo['comissao'] = $resultado['percentual_comissao'];
            $psicologo['data_inicio'] = $resultado['data_inicio'];
            $psicologo['ativo'] = $resultado['ativo'];
        }
    }
} else {
    $psicologo = array(
        'id' => '',
        'pessoa_id' => '',
        'nome' => '',
        'crp' => '',
        'contato' => '',
        'comissao' => 0.50,
        'data_inicio' => '',
        'ativo' => 1
    );
}
include_once ROOT_PATH . '/includes/header.php';

// Definição das opções 
$opcoes_ativo = array(
    0 => 'Não',
    1 => 'Sim'
);

require_once ROOT_PATH . '/modules/form_options.php';
$tmp_form = TEMPLATE_PATH . 'form_psicologo.html';

if (file_exists($tmp_form)) {
    $form = file_get_contents($tmp_form);

    // Trocas os marcadores pela variável $errosValidacao
    $form = str_replace('{ERRO_ID}',          htmlspecialchars($errosValidacao['id'] ?? '', ENT_QUOTES, 'UTF-8'), $form);
    $form = str_replace('{ERRO_PESSOA_ID}',   htmlspecialchars($errosValidacao['pessoa_id'] ?? '', ENT_QUOTES, 'UTF-8'), $form);
    $form = str_replace('{ERRO_ATIVO}',       htmlspecialchars($errosValidacao['ativo'] ?? '', ENT_QUOTES, 'UTF-8'), $form);
    $form = str_replace('{ERRO_NOME}',        htmlspecialchars($errosValidacao['nome'] ?? '', ENT_QUOTES, 'UTF-8'), $form);
    $form = str_replace('{ERRO_CRP}',         htmlspecialchars($errosValidacao['crp'] ?? '', ENT_QUOTES, 'UTF-8'), $form);
    $form = str_replace('{ERRO_CONTATO}',     htmlspecialchars($errosValidacao['contato'] ?? '', ENT_QUOTES, 'UTF-8'), $form);
    $form = str_replace('{ERRO_COMISSAO}',    htmlspecialchars($errosValidacao['comissao'] ?? '', ENT_QUOTES, 'UTF-8'), $form);
    $form = str_replace('{ERRO_DATA_INICIO}', htmlspecialchars($errosValidacao['data_inicio'] ?? '', ENT_QUOTES, 'UTF-8'), $form);

    // Trocas os marcadores pela variável $psicologo
    $form = str_replace('{ID}',          htmlspecialchars($psicologo['id'], ENT_QUOTES, 'UTF-8'), $form);
    $form = str_replace('{PESSOA_ID}',   htmlspecialchars($psicologo['pessoa_id'], ENT_QUOTES, 'UTF-8'), $form);
    $form = str_replace('{ATIVO}',       options_select($opcoes_ativo, $psicologo['ativo']), $form);
    $form = str_replace('{NOME}',        htmlspecialchars($psicologo['nome'], ENT_QUOTES, 'UTF-8'), $form);
    $form = str_replace('{CRP}',         htmlspecialchars($psicologo['crp'], ENT_QUOTES, 'UTF-8'), $form);
    $form = str_replace('{CONTATO}',     htmlspecialchars($psicologo['contato'], ENT_QUOTES, 'UTF-8'), $form);
    $form = str_replace('{COMISSAO}',    htmlspecialchars(number_format($psicologo['comissao'], 2), ENT_QUOTES, 'UTF-8'), $form);
    $form = str_replace('{DATA_INICIO}', htmlspecialchars($psicologo['data_inicio'], ENT_QUOTES, 'UTF-8'), $form);

    print $form;
} else {
    print "Template não encontrado.";
}

include_once ROOT_PATH . '/includes/footer.php';