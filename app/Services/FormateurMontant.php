<?php

namespace App\Services;

class FormateurMontant
{
    public static function euros(float|string|null $montant): string
    {
        $valeur = (float) $montant;
        $decimales = floor($valeur) == $valeur ? 0 : 2;

        return number_format($valeur, $decimales, ',', ' ');
    }
}
