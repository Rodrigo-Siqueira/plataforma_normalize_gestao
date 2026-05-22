<?php

declare(strict_types=1);

namespace Normalize\Session;

use RuntimeException;

/**
 * Classe Sessao
 *
 * Gerenciador orientado a objetos para sessões nativas do PHP ($_SESSION).
 * Configura automaticamente cookies de sessão com boas práticas de segurança (HttpOnly, SameSite),
 * previne ataques de fixação de sessão e fornece um sistema de mensagens instantâneas (Flash Messages)
 * que expiram automaticamente após a próxima requisição.
 *
 * @package Normalize\Session
 */
class Sessao
{
    /**
     * @var string Chave interna utilizada no array $_SESSION para isolar as mensagens flash.
     */
    private const FLASH_CHAVE = '_flash_mensagens';

    /**
     * Construtor da classe Sessao.
     *
     * Inicializa a sessão com as configurações de segurança padrão e processa
     * o ciclo de vida (envelhecimento e remoção) das mensagens flash pendentes.
     *
     * @param array $opcoes Configurações adicionais ou overrides para o session_start().
     */
    public function __construct(array $opcoes = [])
    {
        $this->iniciar($opcoes);
        $this->envelhecerFlash();
    }

    /**
     * Inicializa a sessão do PHP caso ela ainda não esteja ativa.
     *
     * Define diretivas rígidas de segurança por padrão (como HTTP-Only, SameSite Lax e 
     * Secure automático sob protocolo HTTPS) antes de abrir a sessão.
     *
     * @param array $opcoes Configurações para mesclar com as diretivas padrões da sessão.
     * @return void
     * @throws RuntimeException Se o PHP falhar nativamente ao iniciar a sessão.
     */
    public function iniciar(array $opcoes = []): void
    {
        if ($this->ativa()) {
            return;
        }

        $opcoesPadrao = [
            'use_strict_mode'   => 1,
            'use_cookies'       => 1,
            'use_only_cookies'  => 1,
            'cookie_httponly'   => 1,
            'cookie_samesite'   => 'Lax',
        ];

        // Força cookie seguro se estiver rodando em HTTPS
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
            $opcoesPadrao['cookie_secure'] = 1;
        }

        // Mescla as configurações padrão com as recebidas via parâmetro
        $opcoesSessao = array_merge($opcoesPadrao, $opcoes);

        if (!session_start($opcoesSessao)) {
            throw new RuntimeException("Falha crítica: Não foi possível iniciar a sessão.");
        }
    }

    /**
     * Verifica se a sessão atual já foi iniciada e está ativa.
     *
     * @return bool Verdadeiro se a sessão estiver ativa, falso caso contrário.
     */
    public function ativa(): bool
    {
        return session_status() === PHP_SESSION_ACTIVE;
    }

    /**
     * Cria ou atualiza um valor na sessão sob um índice específico.
     *
     * @param string $indice Chave identificadora no array de sessão.
     * @param mixed $valor Conteúdo a ser armazenado.
     * @return self A própria instância para encadeamento de métodos.
     */
    public function criar(string $indice, mixed $valor): self
    {
        $_SESSION[$indice] = $valor;
        return $this;
    }

    /**
     * Obtém um valor armazenado na sessão. Retorna um valor padrão caso o índice não exista.
     *
     * @param string $indice Chave identificadora do dado.
     * @param mixed $padrao Valor de escape caso o índice não seja encontrado. O padrão é null.
     * @return mixed O dado armazenado ou o valor padrão definido.
     */
    public function obter(string $indice, mixed $padrao = null): mixed
    {
        return $this->checar($indice) ? $_SESSION[$indice] : $padrao;
    }

    /**
     * Verifica se uma determinada chave existe na sessão atual.
     *
     * @param string $indice Chave a ser verificada.
     * @return bool Verdadeiro se a chave existir (mesmo que nula), falso caso contrário.
     */
    public function checar(string $indice): bool
    {
        return array_key_exists($indice, $_SESSION);
    }

    /**
     * Remove um índice específico e seu respectivo valor da sessão.
     *
     * @param string $indice Chave a ser destruída.
     * @return self A própria instância para encadeamento de métodos.
     */
    public function limpar(string $indice): self
    {
        if ($this->checar($indice)) {
            unset($_SESSION[$indice]);
        }
        return $this;
    }

    /**
     * Regenera o ID da sessão atual para prevenir ataques de fixação de sessão.
     *
     * @param bool $deletarAntiga Se deve deletar o arquivo de sessão antigo associado ao ID anterior. O padrão é true.
     * @return bool Verdadeiro em caso de sucesso, falso em caso de falha.
     */
    public function regenerar(bool $deletarAntiga = true): bool
    {
        return session_regenerate_id($deletarAntiga);
    }

    /**
     * Destrói completamente a sessão atual.
     *
     * Limpa o array $_SESSION, invalida e expira o cookie de sessão no navegador do cliente
     * e encerra a sessão no servidor.
     *
     * @return self A própria instância para encadeamento de métodos.
     */
    public function deletar(): self
    {
        if ($this->ativa()) {
            $_SESSION = [];

            if (ini_get("session.use_cookies")) {
                $params = session_get_cookie_params();
                setcookie(
                    session_name(),
                    '',
                    time() - 42000,
                    $params["path"],
                    $params["domain"],
                    $params["secure"],
                    $params["httponly"]
                );
            }

            session_destroy();
        }
        return $this;
    }

    // ==========================================
    // GERENCIAMENTO DE MENSAGENS FLASH
    // ==========================================

    /**
     * Cria uma mensagem Flash que persistirá apenas até a próxima requisição.
     *
     * Útil para mensagens de feedback após redirecionamentos (Post-Redirect-Get).
     *
     * @param string $indice Chave identificadora da mensagem flash.
     * @param mixed $valor Conteúdo da mensagem ou dado a ser armazenado temporariamente.
     * @return self A própria instância para encadeamento de métodos.
     */
    public function criarFlash(string $indice, mixed $valor): self
    {
        $_SESSION[self::FLASH_CHAVE][$indice] = [
            'valor' => $valor,
            'remover' => false
        ];
        return $this;
    }

    /**
     * Obtém o valor de uma mensagem Flash sem removê-la manualmente de imediato.
     *
     * @param string $indice Chave identificadora da mensagem flash.
     * @param mixed $padrao Valor de escape caso a mensagem flash não exista. O padrão é null.
     * @return mixed O conteúdo da mensagem flash ou o valor padrão.
     */
    public function obterFlash(string $indice, mixed $padrao = null): mixed
    {
        if (isset($_SESSION[self::FLASH_CHAVE][$indice])) {
            return $_SESSION[self::FLASH_CHAVE][$indice]['valor'];
        }
        return $padrao;
    }

    /**
     * Controla o ciclo de vida das mensagens flash.
     *
     * Remove as mensagens marcadas para remoção (que já passaram por um ciclo de requisição)
     * e marca as novas mensagens para serem eliminadas na próxima execução.
     *
     * @return void
     */
    private function envelhecerFlash(): void
    {
        if (!isset($_SESSION[self::FLASH_CHAVE])) {
            return;
        }

        foreach ($_SESSION[self::FLASH_CHAVE] as $indice => &$flash) {
            if ($flash['remover']) {
                unset($_SESSION[self::FLASH_CHAVE][$indice]);
            } else {
                $flash['remover'] = true;
            }
        }
    }
}
