<?php

namespace App\Services;

use Illuminate\Support\Str;

class NormalisateurNomFichier
{
    private const ARTICLES = ['de', 'du', 'des', 'la', 'le', 'les', 'l', 'd', 'et'];

    public static function segment(string $texte): string
    {
        $ascii = Str::ascii(trim($texte));
        $sansCaracteresSpeciaux = preg_replace('/[^A-Za-z0-9]+/', '-', $ascii);

        return trim($sansCaracteresSpeciaux, '-');
    }

    public static function nomCompact(string $texte): string
    {
        $mots = preg_split('/[^A-Za-z0-9]+/', Str::ascii($texte), -1, PREG_SPLIT_NO_EMPTY);
        $motsSignificatifs = array_filter($mots, fn (string $mot) => ! in_array(strtolower($mot), self::ARTICLES, true));

        return implode('', array_map(fn (string $mot) => ucfirst($mot), $motsSignificatifs));
    }
}
