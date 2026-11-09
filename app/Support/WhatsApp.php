<?php

namespace App\Support;

/**
 * Monta links "clique para conversar" do WhatsApp (wa.me).
 */
class WhatsApp
{
    /** Retorna o link do WhatsApp para o número informado, ou null se o número for inválido. */
    public static function link(?string $phone, ?string $text = null): ?string
    {
        $d = preg_replace('/\D/', '', (string) $phone);
        if (strlen($d) < 10) {
            return null;
        }

        // Números brasileiros sem DDI recebem o 55.
        $url = 'https://wa.me/'.(strlen($d) <= 11 ? '55'.$d : $d);

        return $text ? $url.'?text='.rawurlencode($text) : $url;
    }

    /** Link do WhatsApp comercial (quem vende o sistema), definido em SALES_WHATSAPP no .env. */
    public static function sales(?string $text = null): ?string
    {
        return self::link(config('app.sales_whatsapp'), $text ?? config('app.sales_whatsapp_message'));
    }
}
