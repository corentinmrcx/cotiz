<?php

namespace App\Services;

class NettoyeurHtmlMail
{
    private const BALISES_AUTORISEES = '<b><strong><i><em><u><br><p><div>';

    public static function nettoyer(string $html): string
    {
        $sansBalisesInterdites = strip_tags($html, self::BALISES_AUTORISEES);
        $sansAttributs = preg_replace('/<(\w+)[^>]*>/', '<$1>', $sansBalisesInterdites);

        return trim($sansAttributs);
    }

    public static function enHtml(string $corps): string
    {
        return str_contains($corps, '<') ? self::nettoyer($corps) : nl2br(e($corps));
    }

    public static function enTexte(string $corps): string
    {
        if (! str_contains($corps, '<')) {
            return $corps;
        }

        $avecSauts = preg_replace('/<br\s*\/?>|<\/(p|div)>/', "\n", $corps);

        return trim(html_entity_decode(strip_tags($avecSauts), ENT_QUOTES, 'UTF-8'));
    }
}
