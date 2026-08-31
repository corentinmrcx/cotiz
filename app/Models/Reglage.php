<?php

namespace App\Models;

use App\Enums\CleReglage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class Reglage extends Model
{
    protected $fillable = ['cle', 'valeur'];

    public static function valeur(CleReglage $cle, ?string $defaut = null): ?string
    {
        $reglage = static::query()->where('cle', $cle->value)->first();

        if ($reglage === null || $reglage->valeur === null) {
            return $defaut;
        }

        return $cle->estSensible() ? Crypt::decryptString($reglage->valeur) : $reglage->valeur;
    }

    public static function definir(CleReglage $cle, ?string $valeur): void
    {
        $valeurStockee = ($valeur !== null && $cle->estSensible()) ? Crypt::encryptString($valeur) : $valeur;

        static::query()->updateOrCreate(['cle' => $cle->value], ['valeur' => $valeurStockee]);
    }

    public static function existe(CleReglage $cle): bool
    {
        return static::query()->where('cle', $cle->value)->exists();
    }

    /** @return array<string, ?string> */
    public static function tous(): array
    {
        $valeurs = [];

        foreach (CleReglage::cases() as $cle) {
            $valeurs[$cle->value] = static::valeur($cle);
        }

        return $valeurs;
    }
}
