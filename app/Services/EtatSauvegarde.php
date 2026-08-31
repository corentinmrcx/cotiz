<?php

namespace App\Services;

use App\Models\Adhesion;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class EtatSauvegarde
{
    private const FICHIER = 'derniere-exportation.json';

    public function modificationsNonExportees(): bool
    {
        $marqueur = $this->lireMarqueur();

        if ($marqueur === null) {
            return Adhesion::query()->exists();
        }

        return $marqueur['signature'] !== $this->signature();
    }

    public function derniereExportation(): ?CarbonImmutable
    {
        $marqueur = $this->lireMarqueur();

        return $marqueur === null ? null : CarbonImmutable::parse($marqueur['exportee_le']);
    }

    public function marquerExportee(): void
    {
        Storage::disk('data')->put(self::FICHIER, json_encode([
            'exportee_le' => now()->toIso8601String(),
            'signature' => $this->signature(),
        ]));
    }

    private function signature(): array
    {
        $signature = [];

        foreach (['saisons', 'adhesions', 'destinataires', 'reglages'] as $table) {
            $signature[$table] = md5(DB::table($table)->orderBy('id')->get()->toJson());
        }

        return $signature;
    }

    private function lireMarqueur(): ?array
    {
        if (! Storage::disk('data')->exists(self::FICHIER)) {
            return null;
        }

        $marqueur = json_decode(Storage::disk('data')->get(self::FICHIER), true);

        return is_array($marqueur) && isset($marqueur['exportee_le'], $marqueur['signature']) ? $marqueur : null;
    }
}
