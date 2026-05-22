<?php

namespace Normalize\Helper;

final class Helper
{
    private function __construct() {}
    
    public static function redirecionar(string $url = ''): never
    {
        header('HTTP/1.1 302 Found');

        $local = $url ? self::url($url) : self::url();
        header("Location: {$local}");
        exit();
    }

    public static function url(string $url = ''): string
    {
        $servidor = filter_input(INPUT_SERVER, 'SERVER_NAME');
        $ambiente = $servidor === 'dev.agendamento' ? URL_DESENVOLVIMENTO : URL_PRODUCAO;

        if (str_starts_with($url, '/')) {
            return $ambiente . $url;
        }

        return $ambiente . '/' . $url;
    }

    public static function identifarAmbiente(): bool
    {
        $servidor = filter_input(INPUT_SERVER, 'SERVER_NAME');

        if ($servidor === 'dev.agendamento') {
            return true;
        }
        return false;
    }

    public static function validarUrl(string $url): bool
    {
        if (mb_strlen($url) < 10) {
            return false;
        }

        if (!str_contains($url, '.')) {
            return false;
        }

        if (str_contains($url, 'http://') || str_contains($url, 'https://')) {
            return true;
        }

        return false;
    }

    public static function validarUrlComFiltro(string $url): bool
    {
        return filter_var($url, FILTER_VALIDATE_URL);
    }

    public static function validarEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    public static function formatarValor(float $valor): string
    {
        return number_format(($valor ?: 0), 2, ',', '.');
    }

    function formatarNumero(int $num): string
    {
        return number_format($num ? $num : 0, 0, '.', '.');
    }

    public static function formatarMoeda(float $valor): string
    {
        return 'R$ ' . number_format(($valor ?: 0), 2, ',', '.');
    }
}
