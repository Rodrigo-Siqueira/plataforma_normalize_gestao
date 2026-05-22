<?php
require_once '../db/config.php';

# Transforma erros em exceções
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

# Função busca o produto por ID no banco de dados
function buscar_produto_id($id) {

    try {

        # Criar conexão com banco de dados
        $conn = criar_conexao_db();

        # Criar query que busca o produto por ID
        $sql = "SELECT * FROM produto WHERE id = ?";

        # Preparar a query
        $stmt = mysqli_prepare($conn, $sql);

        #Associar os parametros
        mysqli_stmt_bind_param($stmt, 'i', $id);

        # Executar a query
        mysqli_stmt_execute($stmt);

        # Obter o resultado
        $result = mysqli_stmt_get_result($stmt);

        # Verificar se encontrou o produto
        if ($produto = mysqli_fetch_assoc($result)) {

            return $produto;

        } else {

            return [
                'success' => false,
                'message' => 'Produto não encontrado!'
            ];
        }

    } catch (mysqli_sql_exception $e) {

        # Capturar e retornar a exceção em caso de erro
        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    } finally {

        # Fechar a declaração
        if (isset($stmt) && $stmt) {
            mysqli_stmt_close($stmt);
        }

        # Fechar a conexão com o banco
        if (isset($conn) && $conn) {
            mysqli_close($conn);
        }
    }
    
}

# Função para alterar o produto no banco de dados
function alterar_produto($dados) {
    try {

        # Criar conexão com banco de dados
        $conn = criar_conexao_db();

        # Iniciar a transação
        mysqli_begin_transaction($conn);

        # Criar query que faz a atualização do produto no banco de dados
        $sql = "UPDATE produto SET 
            nome = ?, 
            tipo = ?, 
            numero_sessoes = ?,
            preco = ?,
            validade_dias = ?,
            ativo = ? 
        WHERE id = ?";

        # Preparar a query
        $stmt = mysqli_prepare($conn, $sql);
        #Associar os parametros
        mysqli_stmt_bind_param(
            $stmt,
            'ssidiii',
            $dados['nome'],      #string
            $dados['tipo'],      #string
            $dados['sessoes'],   #int
            $dados['preco'],     #double
            $dados['validade'],  #int
            $dados['ativo'],     #int apenas (0 ou 1)
            $dados['id']         #int
        );
        # Executar a query
        mysqli_stmt_execute($stmt);

        # Fechar a declaração
        mysqli_stmt_close($stmt);

        # Se deu tudo certo confirmar transação
        mysqli_commit($conn);

        # Retornar um sucesso
        return [
            'success' => true,
            'message' => 'Produto alterado com sucesso!'
        ];

    } catch (mysqli_sql_exception $e) {

        # Se aconteceu algum erro retornar a transação
        if (isset($conn) && $conn) {
            mysqli_rollback($conn);
        }

        # Capturar e retornar a exceção em caso de erro        
        return [
            'success' => false,
            'error' => $e->getMessage()
        ];

    } finally {
        # Fechar a conexão com o banco
        if (isset($conn) && $conn) {
            mysqli_close($conn);
        }
    }
}

#Função para adiciona produtos no banco de dados
function adicionar_produto($dados) {

    try {

        # Criar conexão com banco de dados
        $conn = criar_conexao_db();

        # Iniciar a transação
        mysqli_begin_transaction($conn);

        # Criar query que faz a inclusão do produto no banco de dados
        $sql = "INSERT INTO produto (
            nome, 
            tipo, 
            numero_sessoes, 
            preco, 
            validade_dias, 
            ativo)
        VALUES (?, ?, ?, ?, ?, ?)";

        # Preparar a query
        $stmt = mysqli_prepare($conn, $sql);

        #Associar os parametros
        mysqli_stmt_bind_param(
            $stmt, 'ssidii',
            $dados['nome'],      #string
            $dados['tipo'],      #string
            $dados['sessoes'],   #int
            $dados['preco'],     #double
            $dados['validade'],  #int
            $dados['ativo']      #int apenas (0 ou 1)
        );

        # Executar a query
        mysqli_stmt_execute($stmt);

        # Fechar a declaração
        mysqli_stmt_close($stmt);

        # Se deu tudo certo confirmar transação
        mysqli_commit($conn);

        # Retornar um sucesso
        return [
            'success' => true,
            'message' => 'Produto adicionado com sucesso!'
        ];

    } catch (mysqli_sql_exception $e) {

        # Se aconteceu algum erro retornar a transação
        if (isset($conn) && $conn) {
            mysqli_rollback($conn);
        }

        # Capturar e retornar a exceção em caso de erro        
        return [
            'success' => false, 
            'error' => $e->getMessage()
        ];

    } finally {
        # Fechar a conexão com o banco
        if (isset($conn) && $conn) {
            mysqli_close($conn);
        }
    }
}

# Função para listar todos os produtos do banco de dados
function listar_produtos() {

    try {
        # Criar conexão com banco de dados
        $conn = criar_conexao_db();

        # Verificar se a conexão falhou
        if (!$conn) {
            die('Erro de conexão' . mysqli_connect_error());
        }

        # Criar query que lista todos os produtos ordenados por ID
        $sql = "SELECT * FROM produto ORDER BY id";

        # Executar a query 
        $result = mysqli_query($conn, $sql);

        # Pegar todas as linhas do resultado e coloca na variavel $produtos
        $produtos = mysqli_fetch_all($result, MYSQLI_ASSOC);

        # Liberar a memória usada pelo resultado da query
        mysqli_free_result($result);

        # Retorna o array de produtos
        return $produtos;

    } catch (mysqli_sql_exception $e) {

        return [
            'success' => false,
            'error' => $e->getMessage()
        ];

    } finally {

        if (isset($conn) && $conn) {
            mysqli_close($conn);
        }
    }
}