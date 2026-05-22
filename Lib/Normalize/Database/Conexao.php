<?php

declare(strict_types=1);

namespace Normalize\Database;

use Exception;
use PDO;
use PDOException;

/**
 * Classe Conexao
 *
 * Responsável pela criação, configuração e gerenciamento de conexões com o banco de dados.
 * Implementa o padrão Singleton (via escopo estático) para garantir que apenas uma 
 * conexão ativa via PDO exista durante o ciclo de vida da requisição. Suporta múltiplos SGBDs
 * baseando-se em arquivos de configuração estáticos `.ini`.
 *
 * @package Normalize\Database
 */
final class Conexao
{
    /**
     * @var PDO|null Armazena a instância única da conexão PDO ativa, ou null se não iniciada.
     */
    private static ?PDO $conn = null;

    /**
     * Construtor privado para impedir a instanciação direta da classe fora do escopo dela.
     */
    private function __construct() {}

    /**
     * Método clone privado para impedir a clonagem da instância da classe,
     * preservando o comportamento do padrão Singleton.
     * * @return void
     */
    private function __clone() {}

    /**
     * Abre e retorna uma conexão ativa com o banco de dados utilizando um arquivo de configuração.
     *
     * O método busca por um arquivo `.ini` no diretório definido pela constante `CAMINHO_ARQUIVO_INI`.
     * Se a conexão já tiver sido aberta anteriormente, o método apenas retorna a instância existente.
     *
     * Exemplo de estrutura do arquivo de configuração (.ini):
     * ```ini
     * tipo = mysql
     * host = localhost
     * nome = banco
     * usuario = root
     * senha = suasenha
     * porta = 3306
     * ```
     *
     * @param string $arquivoIni Nome do arquivo de configuração (sem a extensão .ini). Padrão é 'normalize'.
     * @return PDO Instância ativa configurada do PDO.
     *
     * @throws Exception Se o arquivo de configuração não for encontrado.
     * @throws Exception Se o tipo de SGBD (driver) especificado não for suportado pelo switch-case.
     * @throws Exception Se uma PDOException for disparada durante a tentativa de conexão ou configuração dos atributos do PDO.
     */
    public static function abrirConexao(string $arquivoIni = 'normalize'): PDO
    {
        try {
            if (empty(self::$conn)) {
                $caminho = CAMINHO_ARQUIVO_INI . "{$arquivoIni}.ini";
                // Verificar se existe o arquivo de configuração para o banco de dados
                if (file_exists($caminho)) {
                    $banco = parse_ini_file($caminho);
                } else {
                    throw new Exception("Arquivo '$arquivoIni' não foi encontrado.");
                }

                // Ler as informações que estão no arquivo
                $host    = $banco['host']    ?? null;
                $nome    = $banco['nome']    ?? null;
                $usuario = $banco['usuario'] ?? null;
                $senha   = $banco['senha']   ?? null;
                $tipo    = $banco['tipo']    ?? null;
                $porta   = $banco['porta']   ?? null;

                // Desconbrir qual tipo de SGBD de banco de dados deve ser utilizado
                switch ($tipo) {
                    case 'pgsql':
                        $porta = $porta ?: '5432';
                        self::$conn = new PDO("pgsql:dbname={$nome}; user={$usuario}; password={$senha};host=$host;port={$porta}");
                        break;
                    case 'mysql':
                        $porta = $porta ?: '3306';
                        self::$conn = new PDO("mysql:host={$host};port={$porta};dbname={$nome}", $usuario, $senha);
                        break;
                    case 'sqlite':
                        self::$conn = new PDO("sqlite:{$nome}");
                        break;
                    case 'ibase':
                        self::$conn = new PDO("firebird:dbname={$nome}", $usuario, $senha);
                        break;
                    case 'oci8':
                        self::$conn = new PDO("oci:dbname={$nome}", $usuario, $senha);
                        break;
                    case 'mssql':
                        self::$conn = new PDO("mssql:host={$host},1433;dbname={$nome}", $usuario, $senha);
                        break;
                    default:
                        throw new Exception("Tipo de banco de dados '$tipo' não suportado.");
                }
            }
            
            // Definir para que o PDO lance exceções na ocorrência de erros
            self::$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            self::$conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            throw new Exception("Erro ao conectar com o banco de dados: " . $e->getCode());
        }

        return self::$conn;
    }
}
