<?php

declare(strict_types=1);

namespace App\Config;

final class Caminhos
{
    private function __construct() {}

    public static function raiz(): string
    {
        return dirname(__DIR__, 2);
    }

    public static function logs(): string
    {
        return self::raiz() . '/storage/logs';
    }

    public static function logAplicacao(): string
    {
        return self::logs() . '/aplicacao.log';
    }

    public static function logErros(): string
    {
        return self::logs() . '/erros.log';
    }

    public static function logDesempenho(): string
    {
        return self::logs() . '/desempenho.log';
    }
}
