<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Saison extends Model
{
    protected $fillable = [
        'libelle',
        'tarif_adulte',
        'tarif_enfant_famille',
        'tarif_enfant_seul',
        'active',
        'visuel_recto',
        'visuel_verso',
    ];

    protected function casts(): array
    {
        return [
            'tarif_adulte' => 'decimal:2',
            'tarif_enfant_famille' => 'decimal:2',
            'tarif_enfant_seul' => 'decimal:2',
            'active' => 'boolean',
        ];
    }

    public function adhesions(): HasMany
    {
        return $this->hasMany(Adhesion::class);
    }

    public static function active(): ?self
    {
        return static::query()->where('active', true)->first();
    }

    public function activer(): void
    {
        static::query()->where('id', '!=', $this->id)->update(['active' => false]);
        $this->update(['active' => true]);
    }

    public function prefixeNumero(): string
    {
        preg_match_all('/\d{4}/', $this->libelle, $annees);

        return implode('', array_map(fn (string $annee) => substr($annee, 2), $annees[0]));
    }

    public function prochainNumero(): string
    {
        $dernierRang = $this->adhesions()
            ->where('numero', 'like', $this->prefixeNumero().'-%')
            ->get()
            ->map(fn (Adhesion $adhesion) => (int) substr($adhesion->numero, strlen($this->prefixeNumero()) + 1))
            ->max() ?? 0;

        return sprintf('%s-%03d', $this->prefixeNumero(), $dernierRang + 1);
    }
}
