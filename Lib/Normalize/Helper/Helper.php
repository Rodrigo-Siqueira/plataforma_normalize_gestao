<?php

namespace Normalize\Helper;

use DateTime;

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

    private static function obterServidorAtual(): string
    {
        $servidor = filter_input(INPUT_SERVER, 'HTTP_HOST');

        if (!is_string($servidor) || $servidor === '') {
            $servidor = filter_input(INPUT_SERVER, 'SERVER_NAME');
        }

        if (!is_string($servidor) || $servidor === '') {
            return 'dev.agendamento';
        }

        return strtolower(
            explode(':', $servidor)[0]);
    }

    public static function url(string $url = ''): string
    {
        $servidor = self::obterServidorAtual();

        $ambiente = match ($servidor) {
            'dev.agendamento' => URL_DESENVOLVIMENTO,
            'app.agendamento' => URL_PRODUCAO,

            default => throw new \RuntimeException(
                'Servidor não reconhecido: ' . $servidor
            ),
        };

        if ($url === '') {
            return $ambiente;
        }

        return $ambiente . '/' . ltrim($url, '/');
    }

    public static function identificarAmbiente(): bool
    {
        $servidor = filter_input(INPUT_SERVER, 'SERVER_NAME');

        return $servidor === 'dev.agendamento';
    }

    public static function validarUrl(string $url): bool
    {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
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

    public static function mascaraCrp(string $crp): string
    {
        $crp = preg_replace('/\D/', '', $crp);

        return preg_replace('/^(\d{2})(.*)$/', '$1/$2', $crp);
    }

    public static function formatarData(string $data): string
    {
        $dataFormatada = new DateTime($data);
        return $dataFormatada->format('d/m/Y');
    }

    public static function formatarTelefone(string $telefone): string
    {
        // Remove tudo que não é número
        $telefone = preg_replace('/[^0-9]/', '', $telefone);
        $tamanho = strlen($telefone);

        // Verifica se é um telefone fixo ou celular com 9 dígitos
        if ($tamanho == 11) {
            return preg_replace('/(\d{2})(\d{5})(\d{4})/', '(\1) \2-\3', $telefone);
        }
        // Verifica se é telefone fixo ou celular antigo com 8 dígitos
        elseif ($tamanho == 10) {
            return preg_replace('/(\d{2})(\d{4})(\d{4})/', '(\1) \2-\3', $telefone);
        }
        // Se tiver outro tamanho, retorna o número original
        return $telefone;
    }
}
