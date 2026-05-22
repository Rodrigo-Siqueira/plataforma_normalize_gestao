<?php
require_once __DIR__ . '/../../config/config.php';
require_once 'regras_paciente.php';
require_once '../psicologo/regras_psicologo.php';

$currentPage = 'paciente';
$psicologos = listar_psicologo();


if (!empty($_REQUEST['action'])) {
    if ($_REQUEST['action'] == 'save') {
        $paciente = $_POST;

        if (empty($paciente['id'])) {
            $resultado = adicionarPaciente($paciente);

            print "<script>
                alert('" . addslashes($resultado) . "');
                window.location.href = 'index.php';
            </script>";
        } else {
            $resultado = alterarPaciente($paciente);

            print "<script>
                alert('" . addslashes($resultado) . "');
                window.location.href = 'index.php';
            </script>";
        }
    } elseif ($_REQUEST['action'] == 'edit') {
        $id = (int) $_GET['id'];

        $resultado = buscarPacienteId($id);

        if ($resultado) {
            $paciente['id'] = $resultado['id'];
            $paciente['pessoaPacienteId'] = $resultado['pessoaPacienteId'];
            $paciente['psicologoId'] = $resultado['psicologoId'];
            $paciente['pacienteNome'] = $resultado['pacienteNome'];
            $paciente['pacienteContato'] = $resultado['pacienteContato'];
            $paciente['ativo'] = $resultado['ativo'];
            $paciente['responsavelId'] = $resultado['responsavelId'];
            $paciente['responsavelPessoaId'] = $resultado['responsavelPessoaId'];
            $paciente['pacienteId'] = $resultado['id'];
            $paciente['responsavelNome'] = $resultado['responsavelNome'];
            $paciente['responsavelContato'] = $resultado['responsavelContato'];
            $paciente['responsavelVinculo'] = $resultado['vinculo'];
        }
    }
} else {
    // Inicializa a variavel $paciente vazios.
    $paciente = array(
        'id' => '',
        'pessoaPacienteId' => '',
        'psicologoId' => '',
        'pacienteNome' => '',
        'pacienteContato' => '',
        'ativo' => 1,
        'responsavelId' => '',
        'responsavelPessoaId' => '',
        'pacienteId' => '',
        'responsavelNome' => '',
        'responsavelContato' => '',
        'responsavelVinculo' => ''
    );
}
include_once ROOT_PATH . '/includes/header.php';

// Definição das opções 
$opcoesAtivo = array(
    0 => 'Não',
    1 => 'Sim'
);

$tipoVinculo = array(
    'PROPRIO' => 'Próprio',
    'PAI' => 'Pai',
    'MAE' => 'Mãe',
    'OUTRO' => 'Outro'
);
require_once ROOT_PATH . '/modules/form_options.php';
$tmpForm = TEMPLATE_PATH . 'form_paciente.html';

if (file_exists($tmpForm)) {
    $formPaciente = file_get_contents($tmpForm);

    // Trocas os marcadores pela variável $errosValidacao
    $formPaciente = str_replace('{ERRO_ID}',                  htmlspecialchars($errosValidacao['id'] ?? '', ENT_QUOTES, 'UTF-8'), $formPaciente);
    $formPaciente = str_replace('{ERRO_PE_PACIENTE_ID}',      htmlspecialchars($errosValidacao['pessoaPacienteId'] ?? '', ENT_QUOTES, 'UTF-8'), $formPaciente);
    $formPaciente = str_replace('{ERRO_ATIVO}',               htmlspecialchars($errosValidacao['ativo'] ?? '', ENT_QUOTES, 'UTF-8'), $formPaciente);
    $formPaciente = str_replace('{ERRO_PACIENTE_NOME}',       htmlspecialchars($errosValidacao['pacienteNome'] ?? '', ENT_QUOTES, 'UTF-8'), $formPaciente);
    $formPaciente = str_replace('{ERRO_PACIENTE_CONTATO}',    htmlspecialchars($errosValidacao['pacienteContato'] ?? '', ENT_QUOTES, 'UTF-8'), $formPaciente);
    $formPaciente = str_replace('{ERRO_SELECT_PSICOLOGO}',    htmlspecialchars($errosValidacao['psicologoId'] ?? '', ENT_QUOTES, 'UTF-8'), $formPaciente);
    $formPaciente = str_replace('{ERRO_RESP_PESSOA_ID}',      htmlspecialchars($errosValidacao['responsavelPessoaId'] ?? '', ENT_QUOTES, 'UTF-8'), $formPaciente);
    $formPaciente = str_replace('{ERRO_PACIENTE_ID}',         htmlspecialchars($errosValidacao['pacienteId'] ?? '', ENT_QUOTES, 'UTF-8'), $formPaciente);
    $formPaciente = str_replace('{ERRO_RESPONSAVEL_ID}',      htmlspecialchars($errosValidacao['responsavelId'] ?? '', ENT_QUOTES, 'UTF-8'), $formPaciente);
    $formPaciente = str_replace('{ERRO_RESPONSAVEL_VINCULO}', htmlspecialchars($errosValidacao['responsavelVinculo'] ?? '', ENT_QUOTES, 'UTF-8'), $formPaciente);
    $formPaciente = str_replace('{ERRO_RESPONSAVEL_NOME}',    htmlspecialchars($errosValidacao['responsavelNome'] ?? '', ENT_QUOTES, 'UTF-8'), $formPaciente);
    $formPaciente = str_replace('{ERRO_RESPONSAVEL_CONTATO}', htmlspecialchars($errosValidacao['responsavelContato'] ?? '', ENT_QUOTES, 'UTF-8'), $formPaciente);


    // Trocas os marcadores pela variável $psicologo
    $formPaciente = str_replace('{ID}',                   htmlspecialchars($paciente['id'], ENT_QUOTES, 'UTF-8'), $formPaciente);
    $formPaciente = str_replace('{PE_PACIENTE_ID}',       htmlspecialchars($paciente['pessoaPacienteId'], ENT_QUOTES, 'UTF-8'), $formPaciente);
    $formPaciente = str_replace('{SELECT_PSICOLOGO}',     selecionarEntidade($psicologos, $paciente['psicologoId']), $formPaciente);
    $formPaciente = str_replace('{RADIO_PROPRIO_RESPONSAVEL}', ($paciente['responsavelVinculo'] == 'PROPRIO' || empty($paciente['responsavelVinculo'])) ? 'checked' : '', $formPaciente);
    $formPaciente = str_replace('{RADIO_OUTRO_RESPONSAVEL}', ($paciente['responsavelVinculo'] != 'PROPRIO' && !empty($paciente['responsavelVinculo'])) ? 'checked' : '', $formPaciente);
    $formPaciente = str_replace('{PACIENTE_NOME}',       htmlspecialchars($paciente['pacienteNome'], ENT_QUOTES, 'UTF-8'), $formPaciente);
    $formPaciente = str_replace('{PACIENTE_CONTATO}',    htmlspecialchars($paciente['pacienteContato'], ENT_QUOTES, 'UTF-8'), $formPaciente);
    $formPaciente = str_replace('{ATIVO}',               options_select($opcoesAtivo, $paciente['ativo']), $formPaciente);
    $formPaciente = str_replace('{RESPONSAVEL_ID}',      htmlspecialchars($paciente['responsavelId'], ENT_QUOTES, 'UTF-8'), $formPaciente);
    $formPaciente = str_replace('{RESP_PESSOA_ID}',      htmlspecialchars($paciente['responsavelPessoaId'], ENT_QUOTES, 'UTF-8'), $formPaciente);
    $formPaciente = str_replace('{PACIENTE_ID}',         htmlspecialchars($paciente['pacienteId'], ENT_QUOTES, 'UTF-8'), $formPaciente);
    $formPaciente = str_replace('{RESPONSAVEL_NOME}',    htmlspecialchars($paciente['responsavelNome'], ENT_QUOTES, 'UTF-8'), $formPaciente);
    $formPaciente = str_replace('{RESPONSAVEL_CONTATO}', htmlspecialchars($paciente['responsavelContato'], ENT_QUOTES, 'UTF-8'), $formPaciente);
    $formPaciente = str_replace('{RESPONSAVEL_VINCULO}', options_select($tipoVinculo, $paciente['responsavelVinculo']), $formPaciente);


    print $formPaciente;
} else {
    print "Template não encontrado.";
}
include_once ROOT_PATH . '/includes/footer.php';
