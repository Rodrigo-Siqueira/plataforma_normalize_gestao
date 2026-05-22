<?php

// Habilitar o modo estrito para tipos
declare(strict_types=1);

// Importar arquivos necessários
require_once ROOT_PATH . '/modules/log/regra_log.php';
require_once ROOT_PATH . '/modules/db/config.php';
require_once ROOT_PATH . '/modules/pessoa/regras_pessoa.php';

// Transforma erros em exceções
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

function listarPacientes(): array
{
    try {
        $conn = criar_conexao_db();
        $sql = "SELECT
            pa.id,
            pePac.id AS pessoaPacienteId,
            pePac.nome_completo AS pacienteNome,
            pePac.contato AS pacienteContato,
            pePsi.nome_completo AS psicologo,
            pa.ativo,
            psi.id AS psicologoId,
            rp.id AS responsavelId,
            peResp.id AS responsavelPessoaId,
            peResp.nome_completo AS responsavelNome,
            peResp.contato AS responsavelContato,
            rp.tipo AS vinculo
            FROM paciente AS pa
            JOIN pessoa AS pePac
                ON pePac.id = pa.pessoa_id
            JOIN responsavel_paciente AS rp
                ON rp.paciente_id = pa.id
            JOIN pessoa AS peResp
                ON peResp.id = rp.responsavel_id
            JOIN psicologo AS psi
                ON psi.id = pa.psicologo_responsavel_id
            JOIN pessoa AS pePsi
                ON pePsi.id = psi.pessoa_id
            ORDER BY pePac.nome_completo";

        $resultado = mysqli_query($conn, $sql);
        $pacientes = mysqli_fetch_all($resultado, MYSQLI_ASSOC);

        return $pacientes ?? [];

    } catch (\Throwable $e) {
        logErro('Erro ao listar pacientes.', $e);
        throw new RuntimeException('Desculpe, ocorreu um erro inesperado ao listar o pacientes.', 0, $e);
    } finally {
        if (isset($conn) && $conn) {
            mysqli_close($conn);
        }
    }
}

function buscarPacienteId(int $id): ?array
{
    $stmt = null;
    try {
        $conn = criar_conexao_db();
        $sql = "SELECT
            pa.id,
            pePac.id AS pessoaPacienteId,
            pePac.nome_completo AS pacienteNome,
            pePac.contato AS pacienteContato,
            pa.ativo,
            psi.id AS psicologoId,
            rp.id AS responsavelId,
            peResp.id AS responsavelPessoaId,
            peResp.nome_completo AS responsavelNome,
            peResp.contato AS responsavelContato,
            rp.tipo AS vinculo
        FROM paciente AS pa
        JOIN pessoa AS pePac
            ON pePac.id = pa.pessoa_id
        JOIN responsavel_paciente AS rp
            ON rp.paciente_id = pa.id
        JOIN pessoa AS peResp
            ON peResp.id = rp.responsavel_id
        JOIN psicologo AS psi
            ON psi.id = pa.psicologo_responsavel_id
        JOIN pessoa AS pePsi
            ON pePsi.id = psi.pessoa_id
        WHERE pa.id = ?";

        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        $resultado = mysqli_stmt_get_result($stmt);
        $paciente = mysqli_fetch_assoc($resultado);
        mysqli_stmt_close($stmt);

        return $paciente ?: null;

    } catch (\Throwable $e) {
        logErro('Erro ao buscar paciente por ID.', $e);
        throw new RuntimeException('Desculpe, ocorreu um erro inesperado ao listar buscar paciente.', 0, $e);

    } finally {
        // Fechar a conexão com o banco
        if (isset($conn) && $conn) {
            mysqli_close($conn);
        }
    }
}

function alterarPaciente(array $dados): string
{
    $stmt = null;
    try {
        $conn = criar_conexao_db();
        mysqli_begin_transaction($conn);
        $pessoa = buscarPessoaPorId($conn, (int) $dados['pessoaPacienteId']);

        // Verifica os dados são diferentes da tabela pessoa
        if (
            $dados['pacienteNome'] !== $pessoa['nome_completo']
            || $dados['pacienteContato'] !== $pessoa['contato']
        ) {
            // Atualiza os dados da tabela pessoa
            atualizarPessoa(
                $conn, 
                (int) $dados['pessoaPacienteId'],
                $dados['pacienteNome'],
                $dados['pacienteContato']
            );
        }

        $sql = "UPDATE paciente SET 
            psicologo_responsavel_id = ?,
            ativo = ? 
        WHERE id = ?";

        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param(
            $stmt,
            'iii',
            $dados['psicologoId'],
            $dados['ativo'],
            $dados['id']
        );
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        // Buscar responsável atual
        $sqlResponsavel = "SELECT * FROM responsavel_paciente WHERE id = ?";
        $stmtResponsavel = mysqli_prepare($conn, $sqlResponsavel);
        mysqli_stmt_bind_param($stmtResponsavel, 'i', $dados['responsavelId']);
        mysqli_stmt_execute($stmtResponsavel);
        $resultado = mysqli_stmt_get_result($stmtResponsavel);
        $responsavel = mysqli_fetch_assoc($resultado);
        mysqli_stmt_close($stmtResponsavel);

        // Verifica alteração de dados do Responsavel
        if ($dados['tipoResponsavel'] === 'PROPRIO') {
            // O paciente começará a pagar por si mesmo ou continua pagando para si.
            $dados['responsavelPessoaId'] = $dados['pessoaPacienteId'];
            $dados['responsavelVinculo'] = 'PROPRIO';
        } else {
            // A responsabilidade é de OUTRA pessoa
            // Verifica se antes era o Próprio paciente
            if ($responsavel['tipo'] === 'PROPRIO') {
                // Era PRÓPRIO, agora é OUTRO
                if (
                    $dados['responsavelNome'] !== $pessoa['nome_completo']
                    || $dados['responsavelContato'] !== $pessoa['contato']
                ) {
                    $dados['responsavelPessoaId'] = buscarOuCriarPessoa(
                        $conn,
                        $dados['responsavelNome'],
                        $dados['responsavelContato']
                    );
                } else {
                    $dados['responsavelPessoaId'] = $pessoa['id'];
                }
            } else {
                // Já era OUTRO e continuou OUTRO. Apenas atualizamos os dados da pessoa existente.
                atualizarPessoa(
                    $conn,
                    (int) $dados['responsavelPessoaId'],
                    $dados['responsavelNome'],
                    $dados['responsavelContato']
                );
            }
        }

        $sql = "UPDATE responsavel_paciente SET
            responsavel_id = ?,
            tipo = ?
        WHERE id = ?";

        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param(
            $stmt,
            'isi',
            $dados['responsavelPessoaId'],
            $dados['responsavelVinculo'],
            $dados['responsavelId']
        );
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        mysqli_commit($conn);

        return 'Paciente alterado com sucesso';
    } catch (mysqli_sql_exception $e) {
        if (isset($conn) && $conn) {
            mysqli_rollback($conn);
        }
        logErro('Erro ao realizar a transação de alterar paciente.', $e);
        throw new RuntimeException('Desculpe, ocorreu um erro inesperado ao alterar o paciente.', 0, $e);
    } finally {
        if (isset($conn) && $conn) {
            mysqli_close($conn);
        }
    }
}

function adicionarPaciente(array $dados): string
{
    $stmt = null;
    try {
        $conn = criar_conexao_db();
        mysqli_begin_transaction($conn);

        // Verificar se já existe registros em pessoa
        $dados['pessoaPacienteId'] = buscarOuCriarPessoa(
            $conn,
            $dados['pacienteNome'],
            $dados['pacienteContato']
        );

        $sql = "INSERT INTO paciente (
            pessoa_id,
            psicologo_responsavel_id,
            ativo)
        VALUES (?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param(
            $stmt,
            'iii',
            $dados['pessoaPacienteId'],
            $dados['psicologoId'],
            $dados['ativo']
        );
        mysqli_stmt_execute($stmt);
        // Pega o ID do paciente para depois adicionar em responsavel_paciente
        $dados['pacienteId'] = mysqli_insert_id($conn);
        mysqli_stmt_close($stmt);

        // Verfica se $pacienteID pegou o ID
        if (!$dados['pacienteId']) {
            throw new RuntimeException("Erro ao obter ID do paciente.");
        }

        if ($dados['tipoResponsavel'] === 'PROPRIO') {
            // Se ele paga para si mesmo, o responsável é a própria pessoa do paciente
            $dados['responsavelPessoaId'] = $dados['pessoaPacienteId'];
            $dados['responsavelVinculo'] = 'PROPRIO';
        } else {
            // Se for uma nova pessoa, cadastramos ela primeiro na tabela pessoa
            // Pega o ID de pessoa responsável para depois adicionar em responsável_paciente
            $dados['responsavelPessoaId'] = buscarOuCriarPessoa(
                $conn,
                $dados['responsavelNome'],
                $dados['responsavelContato']
            );
        }

        $sql = "INSERT INTO responsavel_paciente (
            responsavel_id,
            paciente_id,
            tipo)
        VALUES (?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param(
            $stmt,
            'iis',
            $dados['responsavelPessoaId'],
            $dados['pacienteId'],
            $dados['responsavelVinculo']
        );
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        mysqli_commit($conn);

        return 'Paciente adicionado com sucesso.';
        
    } catch (\Throwable $e) {
        if (isset($conn) && $conn) {
            mysqli_rollback($conn);
        }
        logErro('Erro ao realizar a transação de adição de paciente.', $e);
        throw new RuntimeException('Desculpe, ocorreu um erro inesperado ao salvar o paciente.', 0, $e);

    } finally {
        if (isset($conn) && $conn) {
            mysqli_close($conn);
        }
    }
}
