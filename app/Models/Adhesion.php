<?php

namespace App\Models;

use App\Enums\StatutAdhesion;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Adhesion extends Model
{
    protected $fillable = [
        'saison_id',
        'numero',
        'nom',
        'prenom',
        'nb_adultes',
        'nb_enfants_famille',
        'nb_enfants_seuls',
        'cotisation_calculee',
        'montant_encaisse',
        'mode_reglement',
        'date_reglement',
        'statut',
        'date_envoi',
        'erreur_envoi',
        'chemin_pdf',
        'chemin_png',
    ];

    protected function casts(): array
    {
        return [
            'nb_adultes' => 'integer',
            'nb_enfants_famille' => 'integer',
            'nb_enfants_seuls' => 'integer',
            'cotisation_calculee' => 'decimal:2',
            'montant_encaisse' => 'decimal:2',
            'date_reglement' => 'date',
            'date_envoi' => 'datetime',
            'statut' => StatutAdhesion::class,
        ];
    }

    public function saison(): BelongsTo
    {
        return $this->belongsTo(Saison::class);
    }

    public function destinataires(): HasMany
    {
        return $this->hasMany(Destinataire::class);
    }

    public function nomComplet(): string
    {
        return trim(mb_strtoupper($this->nom).' '.($this->prenom ?? ''));
    }

    public function nbEnfants(): int
    {
        return $this->nb_enfants_famille + $this->nb_enfants_seuls;
    }

    public function emailsDestinataires(): array
    {
        return $this->destinataires->pluck('email')->all();
    }
}
