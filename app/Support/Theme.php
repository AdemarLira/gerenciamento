<?php

namespace App\Support;

/**
 * Gera as variáveis CSS do tema a partir da cor principal da clínica.
 */
class Theme
{
    public static function cssVariables(string $hex): string
    {
        $hex = self::normalize($hex);
        [$r, $g, $b] = self::rgb($hex);

        // Texto sobre a cor de destaque: escuro em cores claras, branco em cores escuras.
        $contrast = self::luminance($r, $g, $b) > 0.55 ? '#0B0B12' : '#FFFFFF';

        return sprintf(
            '--accent:%s;--accent-rgb:%d,%d,%d;--accent-contrast:%s;',
            $hex, $r, $g, $b, $contrast
        );
    }

    public static function normalize(string $hex): string
    {
        $hex = ltrim(trim($hex), '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }

        return preg_match('/^[0-9a-fA-F]{6}$/', $hex) ? '#'.strtoupper($hex) : '#7C5CFF';
    }

    /** @return array{int,int,int} */
    public static function rgb(string $hex): array
    {
        $hex = ltrim(self::normalize($hex), '#');

        return [hexdec(substr($hex, 0, 2)), hexdec(substr($hex, 2, 2)), hexdec(substr($hex, 4, 2))];
    }

    public static function luminance(int $r, int $g, int $b): float
    {
        $c = array_map(function ($v) {
            $v /= 255;

            return $v <= 0.03928 ? $v / 12.92 : (($v + 0.055) / 1.055) ** 2.4;
        }, [$r, $g, $b]);

        return 0.2126 * $c[0] + 0.7152 * $c[1] + 0.0722 * $c[2];
    }
}
