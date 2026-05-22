<?php

declare(strict_types=1);

namespace Normalize\Database;

use Normalize\Log\Logger;
use Exception;
use PDO;

/**
 * Classe Transacao
 *
 * Gerencia o ciclo de vida de transações ACID no banco de dados de forma centralizada.
 * Permite abrir uma transação, obter a conexão ativa de forma segura, commitar (aplicar),
 * dar rollback (desfazer) e acoplar um mecanismo de Log para auditoria das operações.
 *
 * @package Normalize\Database
 */
final class Transacao
{
    /**
     * @var PDO|null Instância da conexão PDO ativa utilizada para a transação corrente.
     */
    private static ?PDO $tConn = null;

    /**
     * @var Logger|null Instância do componente de log para registrar eventos da transação.
     */
    private static ?Logger $logger = null;

    /**
     * Construtor privado para impedir a instanciação direta da classe.
     */
    private function __construct() {}

    /**
     * Método clone privado para impedir a clonagem da classe, mantendo o controle centralizado.
     *
     * @return void
     */
    private function __clone() {}

    /**
     * Inicializa uma nova transação com o banco de dados.
     *
     * Solicita uma conexão à classe `Conexao` e inicia formalmente a transação 
     * isolando as operações seguintes até que sejam confirmadas ou desfeitas.
     *
     * @param string $arquivoIni Nome do arquivo de configuração do banco (sem extensão). Padrão é 'normalize'.
     * @return void
     * @throws Exception Se houver falha ao tentar abrir a conexão ou iniciar a transação.
     */
    public static function abrir(string $arquivoIni = 'normalize'): void
    {
        if (empty(self::$tConn)) {
            self::$tConn = Conexao::abrirConexao($arquivoIni);
            self::$tConn->beginTransaction();
        }
    }

    /**
     * Retorna a conexão ativa que está sob a transação atual.
     *
     * Deve ser utilizado pelas classes de persistência (DAOs/Repositories) para garantir
     * que executem comandos dentro do mesmo escopo isolado da transação.
     *
     * @return PDO Instância ativa do PDO vinculada à transação.
     * @throws Exception Se o método for chamado sem que haja uma transação iniciada.
     */
    public static function pegarConexaoAtiva(): PDO
    {
        if (!self::$tConn) {
            throw new Exception("Nenhuma transação ativa.");
        }

        return self::$tConn;
    }

    /**
     * Confirma (Commit) todas as operações realizadas durante a transação atual.
     *
     * Salva as alterações permanentemente no banco de dados. Independentemente do sucesso
     * ou falha no commit, garante o encerramento e limpeza da conexão ativa via bloco `finally`.
     *
     * @return void
     */
    public static function aplicarOperacao(): void {
        try {
            if (self::$tConn && self::$tConn->inTransaction()) {
                self::$tConn->commit();
            }
        } finally {
            self::fechar();
        }
    }

    /**
     * Desfaz (Rollback) todas as operações realizadas na transação atual.
     *
     * Descarta qualquer modificação feita desde a abertura da transação, retornando o banco
     * ao estado original. Garante o encerramento e limpeza da conexão ativa via bloco `finally`.
     *
     * @return void
     */
    public static function desfazerOperacao(): void
    {
        try {
            if (self::$tConn && self::$tConn->inTransaction()) {
                self::$tConn->rollback();
            }
        } finally {
            self::fechar();
        }
    }

    /**
     * Encerra e limpa a referência da conexão de transação ativa, definindo-a como null.
     *
     * @return void
     */
    public static function fechar(): void
    {
        self::$tConn = null;
    }

    /**
     * Injeta uma instância de Logger para habilitar o rastreamento de eventos da transação.
     *
     * @param Logger $logger Instância do gerenciador de logs.
     * @return void
     */
    public static function definirLogger(Logger $logger): void
    {
        self::$logger = $logger;
    }

    /**
     * Registra uma mensagem de auditoria ou erro caso um Logger tenha sido previamente definido.
     *
     * @param string $mensagem Conteúdo descritivo a ser gravado no log.
     * @return void
     */
    public static function registrarLog(string $mensagem): void
    {
        if (self::$logger) {
            self::$logger->escrever($mensagem);
        }
    }
}
