<?php

namespace App\Enums;

enum NiveauVerdict: string
{
    case Valide = 'valide';
    case Avertissement = 'avertissement';
    case Rejet = 'rejet';
}
